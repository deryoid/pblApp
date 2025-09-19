<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\KunjunganMitra;
use App\Models\Mahasiswa;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KunjunganMitraController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $items = KunjunganMitra::with(['periode','kelompok'])
            ->where('user_id', $user->id)
            ->latest()->get();
        return view('mahasiswa.kunjungan_mitra.index', compact('items'));
    }

    public function create()
    {
        $user = Auth::user();

        $mhs = Mahasiswa::where('user_id', $user->id)->first();
        // Hanya periode Aktif
        $periodes = Periode::where('status_periode','Aktif')->orderBy('created_at','desc')->get();

        // Ambil pasangan (kelompok_id, periode_id) yang dimiliki mahasiswa
        $kelompokOptions = collect();
        if ($mhs) {
            $kelompokOptions = DB::table('kelompok_mahasiswa as km')
                ->join('kelompok as k','k.id','=','km.kelompok_id')
                ->join('periode as p','p.id','=','km.periode_id')
                ->where('km.mahasiswa_id', $mhs->id)
                ->select('k.id as kelompok_id','k.nama_kelompok','p.id as periode_id','p.periode as nama_periode')
                ->orderBy('p.created_at','desc')
                ->get();
        }

        return view('mahasiswa.kunjungan_mitra.create', compact('periodes','kelompokOptions'));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $mhs = Mahasiswa::where('user_id', $user->id)->first();

        $validated = $request->validate([
            'periode_id' => ['required', Rule::exists('periode','id')->where('status_periode','Aktif')],
            'kelompok_id'=> ['required','exists:kelompok,id'],
            'perusahaan' => ['required','string','max:255'],
            'alamat'     => ['required','string','max:255'],
            'tanggal_kunjungan' => ['required','date'],
            'status_kunjungan'  => ['required', Rule::in(['Sudah dikunjungi','Proses Pembicaraan','Tidak ada tanggapan','Ditolak'])],
            'bukti'      => ['nullable','image','mimes:jpeg,png,jpg,gif,webp','max:6144'],
        ]);

        // Validasi bahwa mahasiswa tsb memang anggota kelompok di periode tsb
        if ($mhs) {
            $exists = DB::table('kelompok_mahasiswa')
                ->where('mahasiswa_id', $mhs->id)
                ->where('kelompok_id', $validated['kelompok_id'])
                ->where('periode_id', $validated['periode_id'])
                ->exists();
            if (!$exists) {
                return back()->withErrors(['kelompok_id' => 'Anda bukan anggota kelompok tersebut pada periode terpilih.'])->withInput();
            }
        }

        $payload = [
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'periode_id' => $validated['periode_id'],
            'kelompok_id'=> $validated['kelompok_id'],
            'user_id'    => $user->id,
            'perusahaan' => $validated['perusahaan'],
            'alamat'     => $validated['alamat'],
            'tanggal_kunjungan' => $validated['tanggal_kunjungan'],
            'status_kunjungan'  => $validated['status_kunjungan'],
        ];

        if ($request->hasFile('bukti')) {
            $file = $request->file('bukti');
            $payload['bukti_kunjungan'] = file_get_contents($file->getRealPath());
            $payload['bukti_kunjungan_mime'] = $file->getMimeType();
        }

        KunjunganMitra::create($payload);
        return redirect()->route('mahasiswa.kunjungan.index')->with('status','Data kunjungan berhasil disimpan.');
    }

    public function edit(KunjunganMitra $kunjungan)
    {
        $this->authorizeView($kunjungan);
        // Hanya periode Aktif
        $periodes = Periode::where('status_periode','Aktif')->orderBy('created_at','desc')->get();
        // kelompok options dibatasi ke milik mahasiswa pembuat
        $mhs = Mahasiswa::where('user_id', Auth::id())->first();
        $kelompokOptions = collect();
        if ($mhs) {
            $kelompokOptions = DB::table('kelompok_mahasiswa as km')
                ->join('kelompok as k','k.id','=','km.kelompok_id')
                ->join('periode as p','p.id','=','km.periode_id')
                ->where('km.mahasiswa_id', $mhs->id)
                ->select('k.id as kelompok_id','k.nama_kelompok','p.id as periode_id','p.periode as nama_periode')
                ->orderBy('p.created_at','desc')
                ->get();
        }
        return view('mahasiswa.kunjungan_mitra.edit', compact('kunjungan','periodes','kelompokOptions'));
    }

    public function update(Request $request, KunjunganMitra $kunjungan)
    {
        $this->authorizeView($kunjungan);
        $user = $request->user();
        $mhs = Mahasiswa::where('user_id', $user->id)->first();

        $validated = $request->validate([
            'periode_id' => ['required', Rule::exists('periode','id')->where('status_periode','Aktif')],
            'kelompok_id'=> ['required','exists:kelompok,id'],
            'perusahaan' => ['required','string','max:255'],
            'alamat'     => ['required','string','max:255'],
            'tanggal_kunjungan' => ['required','date'],
            'status_kunjungan'  => ['required', Rule::in(['Sudah dikunjungi','Proses Pembicaraan','Tidak ada tanggapan','Ditolak'])],
            'bukti'      => ['nullable','image','mimes:jpeg,png,jpg,gif,webp','max:6144'],
            'remove_bukti' => ['sometimes','boolean']
        ]);

        if ($mhs) {
            $exists = DB::table('kelompok_mahasiswa')
                ->where('mahasiswa_id', $mhs->id)
                ->where('kelompok_id', $validated['kelompok_id'])
                ->where('periode_id', $validated['periode_id'])
                ->exists();
            if (!$exists) {
                return back()->withErrors(['kelompok_id' => 'Anda bukan anggota kelompok tersebut pada periode terpilih.'])->withInput();
            }
        }

        $kunjungan->fill([
            'periode_id' => $validated['periode_id'],
            'kelompok_id'=> $validated['kelompok_id'],
            'perusahaan' => $validated['perusahaan'],
            'alamat'     => $validated['alamat'],
            'tanggal_kunjungan' => $validated['tanggal_kunjungan'],
            'status_kunjungan'  => $validated['status_kunjungan'],
        ]);

        if ($request->boolean('remove_bukti')) {
            $kunjungan->bukti_kunjungan = null;
            $kunjungan->bukti_kunjungan_mime = null;
        }
        if ($request->hasFile('bukti')) {
            $file = $request->file('bukti');
            $kunjungan->bukti_kunjungan = file_get_contents($file->getRealPath());
            $kunjungan->bukti_kunjungan_mime = $file->getMimeType();
        }

        $kunjungan->save();
        return redirect()->route('mahasiswa.kunjungan.index')->with('status','Data kunjungan berhasil diperbarui.');
    }

    public function destroy(KunjunganMitra $kunjungan)
    {
        $this->authorizeView($kunjungan);
        $kunjungan->delete();
        return back()->with('status','Data kunjungan dihapus.');
    }

    private function authorizeView(KunjunganMitra $item): void
    {
        if ($item->user_id !== Auth::id()) {
            abort(403, 'Tidak diizinkan.');
        }
    }
}
