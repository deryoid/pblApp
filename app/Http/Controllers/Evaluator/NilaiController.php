<?php

namespace App\Http\Controllers\Evaluator;

use App\Http\Controllers\Controller;
use App\Models\EvaluasiDosen;
use App\Models\EvaluasiMitra;
use App\Models\EvaluasiNilaiAP;
use App\Models\Kelompok;
use App\Models\Mahasiswa;
use App\Models\Periode;
use Illuminate\Http\Request;

class NilaiController extends Controller
{
    public function index(Request $request)
    {
        $periodeId = $request->get('periode_id');
        $kelasId = $request->get('kelas_id');
        $search = $request->get('search');

        $periodes = Periode::orderByDesc('id')->get(['id', 'periode']);
        $kelases = \App\Models\Kelas::orderBy('kelas')->get(['id', 'kelas']);

        // Get evaluations dengan relasi yang lengkap
        $evaluationsDosen = EvaluasiDosen::with([
            'mahasiswa:id,nama_mahasiswa,nim',
            'mahasiswa.kelompoks',
            'kelompok:id,nama_kelompok',
            'projectCard:id,title,list_id',
            'evaluator:id,nama_user',
        ])
            ->when($periodeId, fn ($q) => $q->where('periode_id', $periodeId))
            ->get();

        $evaluationsMitra = EvaluasiMitra::with([
            'mahasiswa:id,nama_mahasiswa,nim',
            'mahasiswa.kelompoks',
            'kelompok:id,nama_kelompok',
            'projectCard:id,title,list_id',
            'evaluator:id,nama_user',
        ])
            ->when($periodeId, fn ($q) => $q->where('periode_id', $periodeId))
            ->get();

        // Get data absensi dan presensi (AP)
        $nilaiAP = EvaluasiNilaiAP::with([
            'mahasiswa:id,nama_mahasiswa,nim',
            'mahasiswa.kelompoks',
            'kelompok:id,nama_kelompok',
            'aktivitasList:id,name',
        ])
            ->when($periodeId, fn ($q) => $q->where('periode_id', $periodeId))
            ->get();

        // Get unique mahasiswa from evaluations
        $mahasiswaIds = $evaluationsDosen->pluck('mahasiswa_id')
            ->merge($evaluationsMitra->pluck('mahasiswa_id'))
            ->merge($nilaiAP->pluck('mahasiswa_id'))
            ->unique()
            ->values();

        // Filter mahasiswa by kelas if kelas_id is selected
        if ($kelasId) {
            // Get mahasiswa IDs from kelompok_mahasiswa with selected kelas_id
            $mahasiswaIdsWithKelas = \DB::table('kelompok_mahasiswa')
                ->where('kelas_id', $kelasId)
                ->when($periodeId, fn ($q) => $q->where('periode_id', $periodeId))
                ->pluck('mahasiswa_id')
                ->unique();

            $mahasiswaIds = $mahasiswaIds->intersect($mahasiswaIdsWithKelas);
        }

        $mahasiswas = Mahasiswa::whereIn('id', $mahasiswaIds)
            ->with(['kelompoks' => function ($q) use ($periodeId) {
                if ($periodeId) {
                    $q->wherePivot('periode_id', $periodeId);
                }
            }])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nim', 'like', "%{$search}%")
                        ->orWhere('nama_mahasiswa', 'like', "%{$search}%");
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
            if ($periodeId) {
                $kelompok = $mahasiswa->kelompoks->firstWhere('pivot.periode_id', $periodeId);
            } else {
                // If no periode filter, get the first kelompok
                $kelompok = $mahasiswa->kelompoks->first();
            }

            // Get kelas info from pivot
            $kelasId = $kelompok ? $kelompok->pivot->kelas_id : null;
            $kelas = $kelasId ? \App\Models\Kelas::find($kelasId) : null;

            $mahasiswaNilai->push([
                'mahasiswa' => $mahasiswa,
                'kelompok' => $kelompok,
                'kelas' => $kelas,
                'nilai_aktifitas' => round($avgAP, 2),
                'nilai_proyek' => round($nilaiProject, 2),
                'nilai_akhir' => round($nilaiAkhir, 2),
                'grade' => $grade,
                'total_evaluasi' => $evalDosenMahasiswa->count() + $evalMitraMahasiswa->count(),
                'total_ap' => $countAP,
            ]);
        }

        return view('evaluator.nilai.index', compact(
            'mahasiswaNilai',
            'periodes',
            'kelases',
            'periodeId',
            'kelasId',
            'search'
        ));
    }

    /**
     * Helper function to calculate grade based on score
     */
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
