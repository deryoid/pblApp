<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\EvaluasiDosen;
use App\Models\EvaluasiMitra;
use App\Models\EvaluasiNilaiAP;
use App\Models\Kelompok;
use App\Models\Mahasiswa;
use App\Models\Periode;
use App\Services\GradingService;
use Illuminate\Http\Request;

class NilaiController extends Controller
{
    public function __construct(
        private GradingService $gradingService
    ) {}

    public function index(Request $request)
    {
        $activePeriode = Periode::getLatestActive();

        if ($request->has('periode_id')) {
            $rawPeriode = $request->get('periode_id');
            $periodeId = ($rawPeriode === '' || $rawPeriode === 'all') ? null : (int) $rawPeriode;
        } else {
            $periodeId = $activePeriode?->id;
        }

        $kelasId = $request->get('kelas_id');
        $search = $request->get('search');

        $periodes = Periode::orderByDesc('id')->get(['id', 'periode', 'status_periode']);
        $kelases = \App\Models\Kelas::orderBy('kelas')->get(['id', 'kelas']);

        $user = auth()->user();
        $isDosenOnly = $user && $user->isDosen() && ! $user->isAdmin();
        $accessibleKelompokIds = $isDosenOnly ? Kelompok::accessibleByDosen($user)->pluck('id')->toArray() : null;

        // Get evaluations dengan relasi yang lengkap
        $evaluationsDosen = EvaluasiDosen::with([
            'mahasiswa:id,nama_mahasiswa,nim',
            'mahasiswa.kelompoks',
            'kelompok:id,nama_kelompok',
            'projectCard:id,title,list_id',
            'evaluator:id,nama_user',
        ])
            ->when($periodeId, fn ($q) => $q->where('periode_id', $periodeId))
            ->when($isDosenOnly, fn ($q) => $q->whereIn('kelompok_id', $accessibleKelompokIds))
            ->get();

        $evaluationsMitra = EvaluasiMitra::with([
            'mahasiswa:id,nama_mahasiswa,nim',
            'mahasiswa.kelompoks',
            'kelompok:id,nama_kelompok',
            'projectCard:id,title,list_id',
            'evaluator:id,nama_user',
        ])
            ->when($periodeId, fn ($q) => $q->where('periode_id', $periodeId))
            ->when($isDosenOnly, fn ($q) => $q->whereIn('kelompok_id', $accessibleKelompokIds))
            ->get();

        // Get data absensi dan presensi (AP)
        $nilaiAP = EvaluasiNilaiAP::with([
            'mahasiswa:id,nama_mahasiswa,nim',
            'mahasiswa.kelompoks',
            'kelompok:id,nama_kelompok',
            'aktivitasList:id,name',
        ])
            ->when($periodeId, fn ($q) => $q->where('periode_id', $periodeId))
            ->when($isDosenOnly, fn ($q) => $q->whereIn('kelompok_id', $accessibleKelompokIds))
            ->get();

        // Get unique mahasiswa from evaluations
        $mahasiswaIds = $evaluationsDosen->pluck('mahasiswa_id')
            ->merge($evaluationsMitra->pluck('mahasiswa_id'))
            ->merge($nilaiAP->pluck('mahasiswa_id'))
            ->unique()
            ->values();

        // Filter mahasiswa by kelas if kelas_id is selected
        if ($kelasId) {
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

            // Hitung nilai rata-rata AP kumulatif per aktivitas mingguan
            $avgAP = 0;
            $weeklyAPGroups = $nilaiAPMahasiswa->groupBy('aktivitas_list_id');
            $countAP = $weeklyAPGroups->count();

            if ($countAP > 0) {
                $totalAP = 0;
                foreach ($weeklyAPGroups as $actId => $groupRecords) {
                    $bestRecord = $groupRecords->first(fn ($r) => ! empty($r->w_ap_kehadiran));
                    $kehadiranValue = $this->gradingService->convertAttendanceToValue($bestRecord?->w_ap_kehadiran);

                    $presentasiRecords = $groupRecords->whereNotNull('w_ap_presentasi');
                    $avgPresentasi = $presentasiRecords->isNotEmpty()
                        ? (float) $presentasiRecords->avg('w_ap_presentasi')
                        : 0.0;

                    $nilaiAPItem = $this->gradingService->calculateActivityScore($kehadiranValue, $avgPresentasi);
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

        return view('dosen.nilai.index', compact(
            'mahasiswaNilai',
            'periodes',
            'kelases',
            'periodeId',
            'kelasId',
            'search',
            'activePeriode'
        ));
    }
}
