<?php

namespace App\Http\Controllers\Evaluator;

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

        // Data Akademik Global
        $totalPeriodes = $periodes->count();
        $totalKelas = \App\Models\Kelas::count();
        $totalKelompoks = \App\Models\Kelompok::count();

        // Data Khusus Periode Terpilih
        $activeKelompoks = $selectedPeriode ? \App\Models\Kelompok::where('periode_id', $selectedPeriode->id)->count() : 0;

        // Data Mahasiswa
        $totalMahasiswas = \App\Models\Mahasiswa::count();
        $activeMahasiswas = $selectedPeriode ? \App\Models\Mahasiswa::whereHas('kelompoks', function ($query) use ($selectedPeriode) {
            $query->where('kelompok.periode_id', $selectedPeriode->id);
        })->count() : 0;

        // Data Evaluasi - khusus untuk evaluator
        $totalSesiEvaluasi = \App\Models\EvaluasiMaster::count();
        $activeSesiEvaluasi = $selectedPeriode ? \App\Models\EvaluasiMaster::where('periode_id', $selectedPeriode->id)->count() : 0;

        // Progress evaluasi evaluator
        $pendingEvaluations = 0;
        $completedEvaluations = 0;

        // Data Proyek yang perlu dievaluasi pada periode terpilih
        $totalProyekCards = \App\Models\ProjectCard::count();
        $proyekToEvaluate = $selectedPeriode ? \App\Models\ProjectCard::whereHas('projectList', function ($query) use ($selectedPeriode) {
            $query->where('periode_id', $selectedPeriode->id);
        })->count() : 0;

        // Data Aktivitas yang perlu dievaluasi
        $totalAktivitasLists = \App\Models\AktivitasList::count();
        $aktivitasToEvaluate = $selectedPeriode ? \App\Models\AktivitasList::where('periode_id', $selectedPeriode->id)
            ->where('status_evaluasi', '!=', 'Sudah Evaluasi')
            ->count() : 0;

        // Jadwal evaluasi
        $todayEvaluations = $selectedPeriode ? \App\Models\EvaluasiMaster::where('periode_id', $selectedPeriode->id)
            ->with(['kelompok', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get() : collect();

        // Progress evaluasi per kelompok
        $evaluasiProgress = [];
        $kelompokPeriode = collect();
        if ($selectedPeriode) {
            $kelompoks = \App\Models\Kelompok::with(['mahasiswas.user', 'projectCards'])
                ->where('periode_id', $selectedPeriode->id)
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

        return view('evaluator.index', compact(
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
