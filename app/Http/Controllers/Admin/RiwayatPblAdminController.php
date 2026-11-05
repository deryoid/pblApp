<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\Periode;
use App\Services\RiwayatPblService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiwayatPblAdminController extends Controller
{
    public function __construct(
        protected RiwayatPblService $riwayatService
    ) {}

    /**
     * Tampilkan direktori riwayat PBL seluruh mahasiswa untuk Administrator.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $kelasId = $request->input('kelas_id');
        $periodeId = $request->input('periode_id');
        $perPage = (int) $request->input('per_page', 15);
        if (! in_array($perPage, [15, 25, 50, 100], true)) {
            $perPage = 15;
        }

        $query = Mahasiswa::with(['user', 'kelas', 'kelompoks.periode'])
            ->withCount('kelompoks');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nim', 'like', "%{$search}%")
                    ->orWhere('nama_mahasiswa', 'like', "%{$search}%");
            });
        }

        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }

        if ($periodeId) {
            $query->whereHas('kelompoks', function ($q) use ($periodeId) {
                $q->where('periode_id', $periodeId);
            });
        }

        $mahasiswas = $query->orderBy('nama_mahasiswa', 'asc')->paginate($perPage)->withQueryString();
        $kelasList = Kelas::orderBy('kelas')->get();
        $periodes = Periode::orderByDesc('id')->get();

        return view('admin.riwayat.index', compact(
            'mahasiswas',
            'kelasList',
            'periodes',
            'search',
            'kelasId',
            'periodeId',
            'perPage'
        ));
    }

    /**
     * Tampilkan riwayat PBL detail dari seorang mahasiswa.
     */
    public function show(Mahasiswa $mahasiswa): View
    {
        $data = $this->riwayatService->getRiwayatMahasiswa($mahasiswa);

        return view('admin.riwayat.show', array_merge($data, [
            'role' => 'admin',
            'backRoute' => route('admin.riwayat.index'),
        ]));
    }

    /**
     * Cetak lembar portofolio riwayat PBL.
     */
    public function cetak(Mahasiswa $mahasiswa): View
    {
        $data = $this->riwayatService->getRiwayatMahasiswa($mahasiswa);

        return view('riwayat.cetak', array_merge($data, [
            'backRoute' => route('admin.riwayat.show', $mahasiswa->uuid),
        ]));
    }
}
