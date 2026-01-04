<?php

namespace App\Exports;

use App\Models\EvaluasiDosen;
use App\Models\EvaluasiMitra;
use App\Models\EvaluasiNilaiAP;
use App\Models\Mahasiswa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class NilaiExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles
{
    protected $periodeId;

    protected $kelasId;

    protected $search;

    public function __construct($periodeId = null, $kelasId = null, $search = null)
    {
        $this->periodeId = $periodeId;
        $this->kelasId = $kelasId;
        $this->search = $search;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Get evaluations dengan relasi yang lengkap
        $evaluationsDosen = EvaluasiDosen::with([
            'mahasiswa:id,nama_mahasiswa,nim',
            'mahasiswa.kelompoks',
            'kelompok:id,nama_kelompok',
            'projectCard:id,title,list_id',
            'evaluator:id,nama_user',
        ])
            ->when($this->periodeId, fn ($q) => $q->where('periode_id', $this->periodeId))
            ->get();

        $evaluationsMitra = EvaluasiMitra::with([
            'mahasiswa:id,nama_mahasiswa,nim',
            'mahasiswa.kelompoks',
            'kelompok:id,nama_kelompok',
            'projectCard:id,title,list_id',
            'evaluator:id,nama_user',
        ])
            ->when($this->periodeId, fn ($q) => $q->where('periode_id', $this->periodeId))
            ->get();

        // Get data absensi dan presensi (AP)
        $nilaiAP = EvaluasiNilaiAP::with([
            'mahasiswa:id,nama_mahasiswa,nim',
            'mahasiswa.kelompoks',
            'kelompok:id,nama_kelompok',
            'aktivitasList:id,name',
        ])
            ->when($this->periodeId, fn ($q) => $q->where('periode_id', $this->periodeId))
            ->get();

        // Get unique mahasiswa from evaluations
        $mahasiswaIds = $evaluationsDosen->pluck('mahasiswa_id')
            ->merge($evaluationsMitra->pluck('mahasiswa_id'))
            ->merge($nilaiAP->pluck('mahasiswa_id'))
            ->unique()
            ->values();

        // Filter mahasiswa by kelas if kelas_id is selected
        if ($this->kelasId) {
            // Get mahasiswa IDs from kelompok_mahasiswa with selected kelas_id
            $mahasiswaIdsWithKelas = \DB::table('kelompok_mahasiswa')
                ->where('kelas_id', $this->kelasId)
                ->when($this->periodeId, fn ($q) => $q->where('periode_id', $this->periodeId))
                ->pluck('mahasiswa_id')
                ->unique();

            $mahasiswaIds = $mahasiswaIds->intersect($mahasiswaIdsWithKelas);
        }

        $mahasiswas = Mahasiswa::whereIn('id', $mahasiswaIds)
            ->with(['kelompoks' => function ($q) {
                if ($this->periodeId) {
                    $q->wherePivot('periode_id', $this->periodeId);
                }
            }])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nim', 'like', "%{$this->search}%")
                        ->orWhere('nama_mahasiswa', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('nama_mahasiswa')
            ->get(['id', 'nim', 'nama_mahasiswa', 'user_id']);

        // Group by mahasiswa dan hitung nilai final
        $mahasiswaNilai = collect();

        foreach ($mahasiswas as $mahasiswa) {
            // Get evaluations dosen untuk mahasiswa ini
            $evalDosenMahasiswa = $evaluationsDosen->where('mahasiswa_id', $mahasiswa->id);
            $evalMitraMahasiswa = $evaluationsMitra->where('mahasiswa_id', $mahasiswa->id);
            $nilaiAPMahasiswa = $nilaiAP->where('mahasiswa_id', $mahasiswa->id);

            // Hitung nilai rata-rata dosen
            $avgDosen = $evalDosenMahasiswa->avg('nilai_akhir') ?? 0;

            // Hitung nilai rata-rata mitra
            $avgMitra = $evalMitraMahasiswa->avg('nilai_akhir') ?? 0;

            // Hitung nilai rata-rata AP
            $avgAP = 0;
            $countAP = $nilaiAPMahasiswa->count();

            if ($countAP > 0) {
                $totalAP = 0;
                foreach ($nilaiAPMahasiswa as $nilaiAPRecord) {
                    // Konversi kehadiran ke nilai numerik
                    $kehadiranValue = match ($nilaiAPRecord->w_ap_kehadiran) {
                        'Hadir' => 100,
                        'Izin' => 70,
                        'Sakit' => 60,
                        'Terlambat' => 50,
                        'Tanpa Keterangan' => 0,
                        default => 0,
                    };

                    // Nilai presentasi sudah dalam bentuk numerik
                    $presentasiValue = $nilaiAPRecord->w_ap_presentasi ?? 0;

                    // Hitung nilai AP per aktivitas: 50% kehadiran + 50% presentasi
                    $nilaiAPItem = ($kehadiranValue * 0.5) + ($presentasiValue * 0.5);
                    $totalAP += $nilaiAPItem;
                }
                $avgAP = $totalAP / $countAP;
            }

            // Hitung nilai proyek (80% dosen + 20% mitra)
            $nilaiProject = ($avgDosen * 0.8) + ($avgMitra * 0.2);

            // Hitung nilai akhir (70% proyek + 30% AP)
            $nilaiAkhir = ($nilaiProject * 0.7) + ($avgAP * 0.3);

            // Tentukan grade
            $grade = $this->calculateGrade($nilaiAkhir);

            // Get kelompok info
            if ($this->periodeId) {
                $kelompok = $mahasiswa->kelompoks->firstWhere('pivot.periode_id', $this->periodeId);
            } else {
                // If no periode filter, get the first kelompok
                $kelompok = $mahasiswa->kelompoks->first();
            }

            // Get kelas info from pivot
            $kelasId = $kelompok ? $kelompok->pivot->kelas_id : null;
            $kelas = $kelasId ? \App\Models\Kelas::find($kelasId) : null;

            $mahasiswaNilai->push([
                'No' => $mahasiswaNilai->count() + 1,
                'NIM' => $mahasiswa->nim ?? '-',
                'Nama Mahasiswa' => $mahasiswa->nama_mahasiswa ?? '-',
                'Kelas' => $kelas?->kelas ?? '-',
                'Kelompok' => $kelompok?->nama_kelompok ?? '-',
                'Nilai Aktivitas (30%)' => round($avgAP, 2),
                'Nilai Proyek (70%)' => round($nilaiProject, 2),
                'Nilai Akhir' => round($nilaiAkhir, 2),
            ]);
        }

        return $mahasiswaNilai;
    }

    public function headings(): array
    {
        return [
            'No',
            'NIM',
            'Nama Mahasiswa',
            'Kelas',
            'Kelompok',
            'Nilai Aktivitas (30%)',
            'Nilai Proyek (70%)',
            'Nilai Akhir',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true, 'size' => 12]],
            // Styling header
            'A1:I1' => [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F81BD'],
                ],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            ],
        ];
    }

    private function calculateGrade($score)
    {
        if ($score === null) {
            return null;
        }

        if ($score >= 85) {
            return 'A';
        }
        if ($score >= 75) {
            return 'B';
        }
        if ($score >= 65) {
            return 'C';
        }
        if ($score >= 55) {
            return 'D';
        }

        return 'E';
    }
}
