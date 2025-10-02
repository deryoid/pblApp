<?php

namespace App\Http\Controllers\Evaluator;

use App\Http\Controllers\Controller;
use App\Models\Kelompok;
use App\Models\Periode;

class KelompokController extends Controller
{
    public function index()
    {
        // Ambil periode aktif yang paling sering digunakan oleh kelompok
        $periodeWithKelompok = Periode::where('status_periode', 'Aktif')
            ->whereHas('kelompoks')
            ->first();

        // Jika tidak ada, ambil periode aktif pertama
        $periodeAktif = $periodeWithKelompok ?: Periode::where('status_periode', 'Aktif')->first();

        $kelompoks = Kelompok::with(['periode', 'mahasiswas', 'evaluasiMasters' => function ($query) use ($periodeAktif) {
            if ($periodeAktif) {
                $query->where('periode_id', $periodeAktif->id);
            }
        }])
            ->when($periodeAktif, function ($query) use ($periodeAktif) {
                return $query->where('periode_id', $periodeAktif->id);
            })
            ->get()
            ->sortBy(function ($kelompok) {
                // Check if there are any completed evaluations
                $hasCompletedEval = $kelompok->evaluasiMasters->contains('status', 'Selesai');

                // Priority: incomplete evaluations first, then by name
                return [$hasCompletedEval ? 1 : 0, $kelompok->nama_kelompok];
            })
            ->values();

        return view('evaluator.kelompok.index', compact('kelompoks', 'periodeAktif'));
    }

    public function show(Kelompok $kelompok)
    {
        $kelompok->load(['periode', 'mahasiswas', 'aktivitasLists']);

        return view('evaluator.kelompok.show', compact('kelompok'));
    }
}
