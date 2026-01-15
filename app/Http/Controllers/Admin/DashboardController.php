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

    public function mitraSelesai(\Illuminate\Http\Request $request): \Illuminate\View\View
    {
        // Get active periode
        $activePeriode = \App\Models\Periode::where('status_periode', 'Aktif')->first();

        // Get filter parameters
        $kelompokId = $request->query('kelompok_id');
        $periodeId = $request->query('periode_id', $activePeriode?->id);

        // Build query
        $query = \App\Models\ProjectCard::with(['kelompok', 'periode'])
            ->where('status_proyek', 'Selesai')
            ->whereNotNull('nama_mitra');

        // Apply filters
        if ($kelompokId) {
            $query->where('kelompok_id', $kelompokId);
        }
        if ($periodeId) {
            $query->where('periode_id', $periodeId);
        }

        $mitraSelesai = $query->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($card) {
                return [
                    'id' => $card->id,
                    'uuid' => $card->uuid,
                    'nama_mitra' => $card->nama_mitra,
                    'kontak_mitra' => $card->kontak_mitra,
                    'title' => $card->title,
                    'kelompok' => $card->kelompok?->nama_kelompok ?? '-',
                    'kelompok_id' => $card->kelompok?->id,
                    'periode' => $card->periode?->periode ?? '-',
                    'periode_id' => $card->periode?->id,
                    'skema_pbl' => $card->skema_pbl ?? '-',
                    'progress' => $card->progress ?? 0,
                    'updated_at' => $card->updated_at,
                ];
            });

        // Group by partner name
        $mitraGrouped = $mitraSelesai->groupBy('nama_mitra')->map(function ($items, $namaMitra) {
            return [
                'nama_mitra' => $namaMitra,
                'kontak_mitra' => $items->first()['kontak_mitra'],
                'jumlah_proyek' => $items->count(),
                'proyek' => $items->toArray(),
            ];
        })->sortByDesc('jumlah_proyek');

        // Group by kelompok for summary
        $rekapPerKelompok = \App\Models\ProjectCard::with(['kelompok', 'periode'])
            ->where('status_proyek', 'Selesai')
            ->whereNotNull('nama_mitra')
            ->when($periodeId, function ($query) use ($periodeId) {
                return $query->where('periode_id', $periodeId);
            })
            ->get()
            ->groupBy('kelompok.nama_kelompok')
            ->map(function ($items, $namaKelompok) {
                $mitraUnik = $items->pluck('nama_mitra')->unique();

                return [
                    'nama_kelompok' => $namaKelompok ?: 'Tanpa Kelompok',
                    'kelompok_id' => $items->first()?->kelompok?->id,
                    'jumlah_proyek' => $items->count(),
                    'jumlah_mitra' => $mitraUnik->count(),
                    'mitra' => $mitraUnik->toArray(),
                ];
            })->sortBy('nama_kelompok');

        // Rekap keseluruhan by periode
        $rekapKeseluruhan = \App\Models\ProjectCard::with(['periode'])
            ->where('status_proyek', 'Selesai')
            ->whereNotNull('nama_mitra')
            ->get()
            ->groupBy('periode.periode')
            ->map(function ($items, $namaPeriode) {
                $mitraUnik = $items->pluck('nama_mitra')->unique();

                return [
                    'nama_periode' => $namaPeriode ?: 'Tanpa Periode',
                    'jumlah_proyek' => $items->count(),
                    'jumlah_mitra' => $mitraUnik->count(),
                ];
            })->sortByDesc('nama_periode');

        // Get all kelompok for filter dropdown (only for active/selected periode)
        $allKelompok = \App\Models\Kelompok::when(
            $periodeId,
            function ($query) use ($periodeId) {
                return $query->where('periode_id', $periodeId);
            }
        )->orderBy('nama_kelompok')->get();

        // Get all periode for filter dropdown
        $allPeriode = \App\Models\Periode::orderBy('periode', 'desc')->get();

        return view('admin.mitra-selesai', compact(
            'mitraGrouped',
            'mitraSelesai',
            'rekapPerKelompok',
            'rekapKeseluruhan',
            'kelompokId',
            'periodeId',
            'allKelompok',
            'allPeriode',
            'activePeriode'
        ));
    }

    public function mitraSelesaiExport(\Illuminate\Http\Request $request)
    {
        $kelompokId = $request->query('kelompok_id');
        $periodeId = $request->query('periode_id');

        $fileName = 'mitra-proyek-selesai';

        if ($periodeId) {
            $periode = \App\Models\Periode::find($periodeId);
            if ($periode) {
                $fileName .= '-'.str_replace(' ', '-', strtolower($periode->periode));
            }
        }

        if ($kelompokId) {
            $kelompok = \App\Models\Kelompok::find($kelompokId);
            if ($kelompok) {
                $fileName .= '-'.str_replace(' ', '-', strtolower($kelompok->nama_kelompok));
            }
        }

        $fileName .= '-'.date('Y-m-d').'.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\MitraSelesaiExport($kelompokId, $periodeId),
            $fileName
        );
    }
}
