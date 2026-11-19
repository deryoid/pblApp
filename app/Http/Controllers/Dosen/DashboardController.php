<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index(\Illuminate\Http\Request $request): \Illuminate\View\View
    {
        // Get all periods for the dropdown selector
        $periodes = \App\Models\Periode::orderByDesc('id')->get();
        $activePeriode = $periodes->firstWhere('status_periode', 'Aktif');

        $selectedPeriodeId = $request->query('periode_id');
        $selectedPeriode = null;
        if ($selectedPeriodeId) {
            $selectedPeriode = $periodes->firstWhere('id', (int) $selectedPeriodeId);
        }
        if (! $selectedPeriode) {
            $selectedPeriode = $activePeriode ?? $periodes->first();
        }

        $user = \Illuminate\Support\Facades\Auth::user();
        $isDosenOnly = $user && $user->isDosen() && ! $user->isAdmin();

        $kelompokQuery = \App\Models\Kelompok::query();
        if ($isDosenOnly) {
            $kelompokQuery->accessibleByDosen($user);
        }

        // Data Akademik Global
        $totalPeriodes = $periodes->count();
        $totalKelas = \App\Models\Kelas::count();
        $totalKelompoks = (clone $kelompokQuery)->count();

        // Data Khusus Periode Terpilih
        $activeKelompoks = $selectedPeriode ? (clone $kelompokQuery)->where('periode_id', $selectedPeriode->id)->count() : 0;

        // Data Mahasiswa
        $mahasiswaQuery = \App\Models\Mahasiswa::query();
        if ($isDosenOnly) {
            $mahasiswaQuery->whereHas('kelompoks', function ($q) use ($user) {
                $q->accessibleByDosen($user);
            });
        }
        $totalMahasiswas = (clone $mahasiswaQuery)->count();
        $activeMahasiswas = $selectedPeriode ? (clone $mahasiswaQuery)->whereHas('kelompoks', function ($query) use ($selectedPeriode) {
            $query->where('kelompok.periode_id', $selectedPeriode->id);
        })->count() : 0;

        // Data Evaluasi - khusus untuk evaluator
        $evaluasiMasterQuery = \App\Models\EvaluasiMaster::query();
        if ($isDosenOnly) {
            $evaluasiMasterQuery->whereHas('kelompok', fn ($kq) => $kq->accessibleByDosen($user));
        }
        $totalSesiEvaluasi = (clone $evaluasiMasterQuery)->count();
        $activeSesiEvaluasi = $selectedPeriode ? (clone $evaluasiMasterQuery)->where('periode_id', $selectedPeriode->id)->count() : 0;

        // Progress evaluasi evaluator
        $pendingEvaluations = 0;
        $completedEvaluations = 0;

        // Data Proyek yang perlu dievaluasi pada periode terpilih (dosen hanya menghitung proyek yang diampunya)
        $proyekCardQuery = \App\Models\ProjectCard::query();
        if ($isDosenOnly) {
            $proyekCardQuery->accessibleByDosen($user);
        }

        $totalProyekCards = (clone $proyekCardQuery)->count();
        $proyekToEvaluate = $selectedPeriode ? (clone $proyekCardQuery)->whereHas('projectList', function ($query) use ($selectedPeriode) {
            $query->where('periode_id', $selectedPeriode->id);
        })->count() : 0;

        // Data Aktivitas yang perlu dievaluasi
        $totalAktivitasLists = \App\Models\AktivitasList::count();
        $aktivitasToEvaluate = $selectedPeriode ? \App\Models\AktivitasList::where('periode_id', $selectedPeriode->id)
            ->where('status_evaluasi', '!=', 'Sudah Evaluasi')
            ->count() : 0;

        // Jadwal evaluasi
        $todayEvaluationsQuery = \App\Models\EvaluasiMaster::query();
        if ($isDosenOnly) {
            $todayEvaluationsQuery->whereHas('kelompok', fn ($kq) => $kq->accessibleByDosen($user));
        }
        $todayEvaluations = $selectedPeriode ? (clone $todayEvaluationsQuery)->where('periode_id', $selectedPeriode->id)
            ->with(['kelompok', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get() : collect();

        // Progress evaluasi per kelompok
        $evaluasiProgress = [];
        $kelompokPeriode = collect();
        if ($selectedPeriode) {
            $kelompokListQuery = \App\Models\Kelompok::with(['mahasiswas.user', 'projectCards' => function ($q) use ($isDosenOnly, $user) {
                if ($isDosenOnly) {
                    $q->accessibleByDosen($user);
                }
            }])
                ->where('periode_id', $selectedPeriode->id);

            if ($isDosenOnly) {
                $kelompokListQuery->accessibleByDosen($user);
            }

            $kelompoks = $kelompokListQuery
                ->orderBy('nama_kelompok')
                ->get();

            $kelompokPeriode = $kelompoks;

            foreach ($kelompoks as $kelompok) {
                $sesiEvaluasi = \App\Models\EvaluasiMaster::where('kelompok_id', $kelompok->id)->get();
                $totalSesi = $sesiEvaluasi->count();
                $selesaiSesi = 0;

                $evaluasiProgress[] = [
                    'nama_kelompok' => $kelompok->nama_kelompok,
                    'total_sesi' => $totalSesi,
                    'selesai_sesi' => $selesaiSesi,
                    'progress' => 0,
                ];
            }
        }

        return view('dosen.index', compact(
            'periodes', 'selectedPeriode', 'activePeriode',
            'totalPeriodes', 'totalKelas', 'totalKelompoks', 'activeKelompoks',
            'totalMahasiswas', 'activeMahasiswas',
            'totalSesiEvaluasi', 'activeSesiEvaluasi',
            'pendingEvaluations', 'completedEvaluations',
            'totalProyekCards', 'proyekToEvaluate',
            'totalAktivitasLists', 'aktivitasToEvaluate',
            'todayEvaluations', 'evaluasiProgress', 'kelompokPeriode'
        ));
    }
}
