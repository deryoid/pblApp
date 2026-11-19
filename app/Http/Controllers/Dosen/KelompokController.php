<?php

namespace App\Http\Controllers\Dosen;

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
        $periodeAktif = Periode::getLatestActive();

        // Ambil periode yang dipilih dari request, default ke periode aktif saat ini
        if ($request->has('periode_id')) {
            $rawPeriode = $request->get('periode_id');
            if ($rawPeriode === '' || $rawPeriode === 'all') {
                $selectedPeriode = null;
                $qPeriode = null;
            } else {
                $selectedPeriode = Periode::find($rawPeriode);
                $qPeriode = $selectedPeriode?->id;
            }
        } else {
            $selectedPeriode = $periodeAktif;
            $qPeriode = $periodeAktif?->id;
        }

        $user = auth()->user();

        $kelompoks = Kelompok::accessibleByDosen($user)
            ->with(['periode', 'mahasiswas.kelas', 'evaluasiMasters' => function ($query) use ($selectedPeriode) {
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

        return view('dosen.kelompok.index', compact('kelompoks', 'periodeAktif', 'periodes', 'qPeriode'));
    }

    public function show(Kelompok $kelompok)
    {
        abort_unless($kelompok->isAccessibleByDosen(auth()->user()), 403, 'Anda tidak memiliki wewenang untuk melihat data kelompok ini.');

        $kelompok->load(['periode', 'mahasiswas.kelas', 'aktivitasLists']);

        return view('dosen.kelompok.show', compact('kelompok'));
    }
}
