<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        // Get active period
        $activePeriode = \App\Models\Periode::where('status_periode', 'Aktif')->first();

        // Data Akademik
        $totalPeriodes = \App\Models\Periode::count();
        $activePeriodeCount = \App\Models\Periode::where('status_periode', 'Aktif')->count();
        $totalKelas = \App\Models\Kelas::count();
        $totalKelompoks = \App\Models\Kelompok::count();
        $activeKelompoks = $activePeriode ? \App\Models\Kelompok::where('periode_id', $activePeriode->id)->count() : 0;

        // Data Pengguna
        $totalUsers = \App\Models\User::count();
        $totalAdmins = \App\Models\User::where('role', 'admin')->count();
        $totalEvaluators = \App\Models\User::where('role', 'evaluator')->count();
        $totalMahasiswas = \App\Models\Mahasiswa::count();
        $activeMahasiswas = $activePeriode ? \App\Models\Mahasiswa::whereHas('kelompoks', function ($query) use ($activePeriode) {
            $query->where('kelompok.periode_id', $activePeriode->id);
        })->count() : 0;

        // Data Evaluasi
        $totalSesiEvaluasi = \App\Models\EvaluasiMaster::count();
        $activeSesiEvaluasi = $activePeriode ? \App\Models\EvaluasiMaster::where('periode_id', $activePeriode->id)->count() : 0;

        // Data Kunjungan Mitra
        $totalKunjungan = \App\Models\KunjunganMitra::count();
        $kunjunganPerPeriode = $activePeriode ? \App\Models\KunjunganMitra::where('periode_id', $activePeriode->id)->count() : 0;

        // Status Kunjungan
        $statusKunjungan = $activePeriode ? \App\Models\KunjunganMitra::where('periode_id', $activePeriode->id)
            ->selectRaw('status_kunjungan, COUNT(*) as count')
            ->groupBy('status_kunjungan')
            ->pluck('count', 'status_kunjungan')
            ->toArray() : [];

        // Data Proyek
        $totalProyekLists = \App\Models\ProjectList::count();
        $totalProyekCards = \App\Models\ProjectCard::count();
        $proyekPerPeriode = $activePeriode ? \App\Models\ProjectList::where('periode_id', $activePeriode->id)->count() : 0;

        // Status Proyek Cards
        $statusProyek = $activePeriode ? \App\Models\ProjectCard::whereHas('projectList', function ($query) use ($activePeriode) {
            $query->where('periode_id', $activePeriode->id);
        })->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray() : [];

        // Data Aktivitas
        $totalAktivitasLists = \App\Models\AktivitasList::count();
        $aktivitasPerPeriode = $activePeriode ? \App\Models\AktivitasList::where('periode_id', $activePeriode->id)->count() : 0;

        // Status Evaluasi Aktivitas
        $statusEvaluasiAktivitas = $activePeriode ? \App\Models\AktivitasList::where('periode_id', $activePeriode->id)
            ->selectRaw('status_evaluasi, COUNT(*) as count')
            ->groupBy('status_evaluasi')
            ->pluck('count', 'status_evaluasi')
            ->toArray() : [];

        // Recent Activities (last 5)
        $recentKunjungan = \App\Models\KunjunganMitra::with(['kelompok', 'user'])
            ->when($activePeriode, function ($query) use ($activePeriode) {
                return $query->where('periode_id', $activePeriode->id);
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

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

        return view('admin.index', compact(
            'activePeriode',
            'totalPeriodes', 'activePeriodeCount', 'totalKelas', 'totalKelompoks', 'activeKelompoks',
            'totalUsers', 'totalAdmins', 'totalEvaluators', 'totalMahasiswas', 'activeMahasiswas',
            'totalSesiEvaluasi', 'activeSesiEvaluasi',
            'totalKunjungan', 'kunjunganPerPeriode', 'statusKunjungan',
            'totalProyekLists', 'totalProyekCards', 'proyekPerPeriode', 'statusProyek',
            'totalAktivitasLists', 'aktivitasPerPeriode', 'statusEvaluasiAktivitas',
            'recentKunjungan', 'evaluasiProgress'
        ));
    }
}
