<?php

namespace App\Http\Controllers\Evaluator;

use App\Http\Controllers\Controller;
use App\Models\Kelompok;
use App\Models\Periode;

class EvaluasiController extends Controller
{
    public function index()
    {
        // Ambil periode aktif yang paling sering digunakan oleh kelompok
        $periodeWithKelompok = Periode::where('status_periode', 'Aktif')
            ->whereHas('kelompoks')
            ->first();

        // Jika tidak ada, ambil periode aktif pertama
        $periodeAktif = $periodeWithKelompok ?: Periode::where('status_periode', 'Aktif')->first();

        $kelompoks = Kelompok::with(['periode', 'mahasiswas.user', 'aktivitasLists'])
            ->when($periodeAktif, function ($query) use ($periodeAktif) {
                return $query->where('periode_id', $periodeAktif->id);
            })
            ->get();

        return view('evaluator.evaluasi.index', compact('kelompoks', 'periodeAktif'));
    }

    public function show(Kelompok $kelompok)
    {
        $kelompok->load(['periode', 'mahasiswas.user', 'aktivitasLists']);

        return view('evaluator.evaluasi.show', compact('kelompok'));
    }
}
