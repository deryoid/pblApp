<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Services\RiwayatPblService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RiwayatPblController extends Controller
{
    public function __construct(
        protected RiwayatPblService $riwayatService
    ) {}

    /**
     * Tampilkan riwayat PBL personal mahasiswa yang sedang login.
     */
    public function index(Request $request): View
    {
        $mahasiswa = Mahasiswa::where('user_id', Auth::id())->first();

        if (! $mahasiswa) {
            return view('mahasiswa.riwayat.index', [
                'mahasiswa' => null,
                'riwayat' => collect(),
                'summary' => [
                    'total_kelompok' => 0,
                    'total_proyek' => 0,
                    'total_aktivitas' => 0,
                    'avg_nilai_akhir' => null,
                    'overall_grade' => null,
                    'overall_kesimpulan' => 'Data mahasiswa tidak ditemukan.',
                ],
            ]);
        }

        $data = $this->riwayatService->getRiwayatMahasiswa($mahasiswa);

        return view('mahasiswa.riwayat.index', $data);
    }

    /**
     * Cetak lembar resmi riwayat portofolio PBL.
     */
    public function cetak(Request $request): View
    {
        $mahasiswa = Mahasiswa::where('user_id', Auth::id())->firstOrFail();
        $data = $this->riwayatService->getRiwayatMahasiswa($mahasiswa);

        return view('riwayat.cetak', array_merge($data, ['backRoute' => route('mahasiswa.riwayat.index')]));
    }
}
