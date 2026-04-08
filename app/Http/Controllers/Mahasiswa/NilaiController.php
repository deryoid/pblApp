<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\EvaluasiDosen;
use App\Models\EvaluasiMitra;
use App\Models\EvaluasiNilaiAP;
use App\Models\Kelompok;
use App\Models\Mahasiswa;
use App\Models\Periode;
use App\Services\GradingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        // Get current mahasiswa
        $mhs = Mahasiswa::where('user_id', Auth::id())->first();

        if (! $mhs) {
            return view('mahasiswa.nilai.index', [
                'mahasiswaNilai' => collect(),
                'periodes' => collect(),
                'kelompoks' => collect(),
                'periodeId' => $periodeId,
                'kelompokId' => null,
                'search' => $search,
            ]);
        }

        $periodes = Periode::orderByDesc('id')->get(['id', 'periode']);

        // Only show kelas that current mahasiswa belongs to
        $kelasOptions = collect();
        if ($mhs) {
            $kelasOptions = \App\Models\Kelas::when($mhs->kelas_id, fn ($q) => $q->where('id', $mhs->kelas_id))
                ->orderBy('kelas')
                ->get(['id', 'kelas']);
        }

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

        // Filter evaluations to only show current mahasiswa's data
        $evaluationsDosen = $evaluationsDosen->where('mahasiswa_id', $mhs->id);
        $evaluationsMitra = $evaluationsMitra->where('mahasiswa_id', $mhs->id);
        $nilaiAP = $nilaiAP->where('mahasiswa_id', $mhs->id);

        // Only use current mahasiswa ID
        $mahasiswaIds = collect([$mhs->id]);

        // Filter mahasiswa by kelas if kelas_id is selected
        if ($kelasId) {
            $mahasiswaIdsWithKelas = Mahasiswa::where('kelas_id', $kelasId)
                ->where('id', $mhs->id)
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

        return view('mahasiswa.nilai.index', compact(
            'mahasiswaNilai',
            'periodes',
            'kelasOptions',
            'periodeId',
            'kelasId',
            'search'
        ));
    }
}
