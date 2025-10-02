<?php

namespace App\Http\Controllers\Evaluator;

use App\Http\Controllers\Controller;
use App\Models\Kelompok;
use App\Models\Periode;
use Illuminate\Http\Request;

class KelompokController extends Controller
{
    public function index(Request $request)
    {
        // Ambil semua periode untuk dropdown
        $periodes = Periode::orderByDesc('id')->get(['id', 'periode', 'status_periode']);

        // Ambil periode aktif yang paling sering digunakan oleh kelompok
        $periodeWithKelompok = Periode::where('status_periode', 'Aktif')
            ->whereHas('kelompoks')
            ->first();

        // Jika tidak ada, ambil periode aktif pertama
        $periodeAktif = $periodeWithKelompok ?: Periode::where('status_periode', 'Aktif')->first();

        // Ambil periode yang dipilih dari request
        $qPeriode = $request->get('periode_id');
        if ($qPeriode) {
            $selectedPeriode = Periode::find($qPeriode);
        } else {
            $selectedPeriode = $periodeAktif;
        }

        $kelompoks = Kelompok::with(['periode', 'mahasiswas', 'evaluasiMasters' => function ($query) use ($selectedPeriode) {
            if ($selectedPeriode) {
                $query->where('periode_id', $selectedPeriode->id);
            }
        }])
            ->when($selectedPeriode, function ($query) use ($selectedPeriode) {
                return $query->where('periode_id', $selectedPeriode->id);
            })
            ->get()
            ->sortBy(function ($kelompok) {
                // Check if there are any completed evaluations
                $hasCompletedEval = $kelompok->evaluasiMasters->contains('status', 'Selesai');

                // Priority: incomplete evaluations first, then by name
                return [$hasCompletedEval ? 1 : 0, $kelompok->nama_kelompok];
            })
            ->values();

        return view('evaluator.kelompok.index', compact('kelompoks', 'periodeAktif', 'periodes', 'qPeriode'));
    }

    public function show(Kelompok $kelompok)
    {
        $kelompok->load(['periode', 'mahasiswas', 'aktivitasLists']);

        return view('evaluator.kelompok.show', compact('kelompok'));
    }
}
