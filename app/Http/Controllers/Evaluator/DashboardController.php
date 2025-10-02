<?php

namespace App\Http\Controllers\Evaluator;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        // Get active period
        $activePeriode = \App\Models\Periode::where('status_periode', 'Aktif')->first();

        // Data Akademik
        $totalPeriodes = \App\Models\Periode::count();
        $totalKelas = \App\Models\Kelas::count();
        $totalKelompoks = \App\Models\Kelompok::count();
        $activeKelompoks = $activePeriode ? \App\Models\Kelompok::where('periode_id', $activePeriode->id)->count() : 0;

        // Data Mahasiswa
        $totalMahasiswas = \App\Models\Mahasiswa::count();
        $activeMahasiswas = $activePeriode ? \App\Models\Mahasiswa::whereHas('kelompoks', function ($query) use ($activePeriode) {
            $query->where('kelompok.periode_id', $activePeriode->id);
        })->count() : 0;

        // Data Evaluasi - khusus untuk evaluator
        $totalSesiEvaluasi = \App\Models\EvaluasiMaster::count();
        $activeSesiEvaluasi = $activePeriode ? \App\Models\EvaluasiMaster::where('periode_id', $activePeriode->id)->count() : 0;

        // Progress evaluasi evaluator
        $pendingEvaluations = $activePeriode ? \App\Models\EvaluasiMaster::where('periode_id', $activePeriode->id)
            ->where('status', 'Pending')
            ->count() : 0;
        $completedEvaluations = $activePeriode ? \App\Models\EvaluasiMaster::where('periode_id', $activePeriode->id)
            ->where('status', 'Selesai')
            ->count() : 0;

        // Data Proyek yang perlu dievaluasi
        $totalProyekCards = \App\Models\ProjectCard::count();
        $proyekToEvaluate = $activePeriode ? \App\Models\ProjectCard::whereHas('projectList', function ($query) use ($activePeriode) {
            $query->where('periode_id', $activePeriode->id)->where('status', '!=', 'Selesai');
        })->count() : 0;

        // Data Aktivitas yang perlu dievaluasi
        $totalAktivitasLists = \App\Models\AktivitasList::count();
        $aktivitasToEvaluate = $activePeriode ? \App\Models\AktivitasList::where('periode_id', $activePeriode->id)
            ->where('status_evaluasi', '!=', 'Sudah Evaluasi')
            ->count() : 0;

        // Jadwal evaluasi hari ini (upcoming)
        $todayEvaluations = $activePeriode ? \App\Models\EvaluasiMaster::where('periode_id', $activePeriode->id)
            ->whereDate('tanggal', \Carbon\Carbon::today())
            ->with(['kelompok', 'user'])
            ->orderBy('tanggal')
            ->limit(5)
            ->get() : collect();

        // Progress evaluasi per kelompok
        $evaluasiProgress = [];
        if ($activePeriode) {
            $kelompoks = \App\Models\Kelompok::where('periode_id', $activePeriode->id)->get();
            foreach ($kelompoks as $kelompok) {
                $sesiEvaluasi = \App\Models\EvaluasiMaster::where('kelompok_id', $kelompok->id)->get();
                $totalSesi = $sesiEvaluasi->count();
                $selesaiSesi = $sesiEvaluasi->where('status', 'Selesai')->count();

                $evaluasiProgress[] = [
                    'nama_kelompok' => $kelompok->nama_kelompok,
                    'total_sesi' => $totalSesi,
                    'selesai_sesi' => $selesaiSesi,
                    'progress' => $totalSesi > 0 ? round(($selesaiSesi / $totalSesi) * 100) : 0,
                ];
            }
        }

        return view('evaluator.index', compact(
            'activePeriode',
            'totalPeriodes', 'totalKelas', 'totalKelompoks', 'activeKelompoks',
            'totalMahasiswas', 'activeMahasiswas',
            'totalSesiEvaluasi', 'activeSesiEvaluasi',
            'pendingEvaluations', 'completedEvaluations',
            'totalProyekCards', 'proyekToEvaluate',
            'totalAktivitasLists', 'aktivitasToEvaluate',
            'todayEvaluations', 'evaluasiProgress'
        ));
    }
}
