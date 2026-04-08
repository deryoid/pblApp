<?php

namespace App\Http\Controllers\Admin;

use App\Exports\NilaiExport;
use App\Http\Controllers\Controller;
use App\Models\EvaluasiDosen;
use App\Models\EvaluasiMitra;
use App\Models\EvaluasiNilaiAP;
use App\Models\Kelompok;
use App\Models\Mahasiswa;
use App\Models\Periode;
use App\Services\GradingService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class NilaiController extends Controller
{
    public function __construct(
        private GradingService $gradingService
    ) {}

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
            // Get mahasiswa IDs with selected kelas_id directly from mahasiswa table
            $mahasiswaIdsWithKelas = Mahasiswa::where('kelas_id', $kelasId)
                ->pluck('id')
                ->unique();

            $mahasiswaIds = $mahasiswaIds->intersect($mahasiswaIdsWithKelas);
        }

        $mahasiswas = Mahasiswa::whereIn('id', $mahasiswaIds)
            ->with(['kelompoks' => function ($q) use ($periodeId) {
                if ($periodeId) {
                    $q->wherePivot('periode_id', $periodeId);
                }
            }, 'kelas'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nim', 'like', "%{$search}%")
                        ->orWhere('nama_mahasiswa', 'like', "%{$search}%");
                });
            })
            ->orderBy('nama_mahasiswa')
            ->get(['id', 'nim', 'nama_mahasiswa', 'user_id', 'kelas_id']);

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
                    $kehadiranValue = $this->gradingService->convertAttendanceToValue($nilaiAPRecord->w_ap_kehadiran);
                    $presentasiValue = $nilaiAPRecord->w_ap_presentasi ?? 0;

                    // Hitung nilai AP per aktivitas: 50% kehadiran + 50% presentasi
                    $nilaiAPItem = $this->gradingService->calculateActivityScore($kehadiranValue, $presentasiValue);
                    $totalAP += $nilaiAPItem;
                }
                $avgAP = $totalAP / $countAP;
            }

            // Hitung nilai proyek (80% dosen + 20% mitra)
            $nilaiProject = $this->gradingService->calculateProjectScore($avgDosen, $avgMitra);

            // Hitung nilai akhir (70% proyek + 30% AP)
            $nilaiAkhir = $this->gradingService->calculateFinalScore($nilaiProject, $avgAP);

            // Tentukan grade
            $grade = $this->gradingService->calculateGrade($nilaiAkhir);

            // Get kelompok info
            if ($periodeId) {
                $kelompok = $mahasiswa->kelompoks->firstWhere('pivot.periode_id', $periodeId);
            } else {
                // If no periode filter, get the first kelompok
                $kelompok = $mahasiswa->kelompoks->first();
            }

            // Get kelas info directly from mahasiswa model
            $kelasId = $mahasiswa->kelas_id;
            $kelas = $mahasiswa->kelas;

            $mahasiswaNilai->push([
                'mahasiswa' => $mahasiswa,
                'kelompok' => $kelompok,
                'kelas' => $kelas,
                'nilai_aktifitas' => $this->gradingService->formatScore($avgAP),
                'nilai_proyek' => $this->gradingService->formatScore($nilaiProject),
                'nilai_akhir' => $this->gradingService->formatScore($nilaiAkhir),
                'grade' => $grade,
                'total_evaluasi' => $evalDosenMahasiswa->count() + $evalMitraMahasiswa->count(),
                'total_ap' => $countAP,
            ]);
        }

        return view('admin.nilai.index', compact(
            'mahasiswaNilai',
            'periodes',
            'kelases',
            'periodeId',
            'kelasId',
            'search'
        ));
    }

    /**
     * Export nilai to Excel dengan filter
     */
    public function export(Request $request): BinaryFileResponse
    {
        $periodeId = $request->get('periode_id');
        $kelasId = $request->get('kelas_id');
        $search = $request->get('search');

        $fileName = 'nilai-mahasiswa-';
        $fileName .= $periodeId ? Periode::find($periodeId)?->periode.'-' : 'semua-';
        $fileName .= $kelasId ? \App\Models\Kelas::find($kelasId)?->kelas.'-' : 'semua-';
        $fileName .= date('YmdHis').'.xlsx';

        return Excel::download(
            new NilaiExport($periodeId, $kelasId, $search),
            $fileName
        );
    }
}
