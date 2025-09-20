<?php

namespace App\Http\Controllers\Admin;

use App\Models\Kelompok;
use App\Models\Mahasiswa;
use App\Models\Periode;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class DataKelompokAdminController extends Controller
{
    public function index(Request $request)
    {
        $periodes = Periode::orderByDesc('id')->get(['id','periode','status_periode']);
        $activePeriode = Periode::where('status_periode','Aktif')->orderByDesc('id')->first();

        $qPeriode = $request->get('periode_id') ?: ($activePeriode?->id);

        $query = Kelompok::with('periode')->withCount('mahasiswas')->latest('nama_kelompok');
        if ($qPeriode) $query->where('periode_id', $qPeriode);

        $data = $query->get();

        return view('admin.kelompok.index', compact('data','periodes','qPeriode','activePeriode'));
    }

    public function create(Request $request)
    {
        // Hanya periode aktif
        $periodes = Periode::where('status_periode', 'Aktif')
            ->orderByDesc('id')
            ->get(['id','periode']);

        if ($periodes->isEmpty()) {
            Alert::warning('Periode belum ada', 'Silakan buat atau set Periode menjadi Aktif terlebih dahulu.');
            return redirect()->route('periode.index');
        }

        // Periode terpilih: dari query ?periode_id=..., default ke periode aktif pertama
        $selectedPeriodeId = (int) ($request->get('periode_id') ?: $periodes->first()->id);

        $kelasList = Kelas::orderBy('kelas')->get(['id','kelas']);

        // Ambil id mahasiswa yang sudah terpakai di periode terpilih
        $takenMahasiswaIds = DB::table('kelompok_mahasiswa')
            ->where('periode_id', $selectedPeriodeId)
            ->pluck('mahasiswa_id');

        // Tampilkan hanya mahasiswa yang BELUM tergabung di periode tsb
        $mahasiswas = Mahasiswa::whereNotIn('id', $takenMahasiswaIds)
            ->orderBy('nama_mahasiswa')
            ->get(['id','nim','nama_mahasiswa']);

        return view('admin.kelompok.create', compact('periodes','kelasList','mahasiswas','selectedPeriodeId'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'periode_id'          => ['required', Rule::exists('periode','id')->where('status_periode', 'Aktif')],
            // nama_kelompok opsional; bila diisi harus unik per-periode
            'nama_kelompok'       => [
                'nullable','string','max:100',
                Rule::unique('kelompok','nama_kelompok')
                    ->where(fn($q)=>$q->where('periode_id',$request->periode_id)),
            ],
            'link_drive'          => ['nullable','url','max:255'],
            'ketua_nim'           => ['nullable','string','max:50'],

            'entries'             => ['required','array','min:1'],
            'entries.*.nim'       => ['required','string','max:50','distinct'],
            'entries.*.kelas_id'  => ['required','integer','exists:kelas,id'],
        ], [
            'entries.required'       => 'Minimal satu anggota diperlukan.',
            'entries.*.nim.distinct' => 'Ada NIM yang dobel pada daftar anggota.',
            'nama_kelompok.unique'   => 'Nama kelompok sudah dipakai pada periode ini.',
        ]);

        if ($validator->fails()) {
            Alert::toast($validator->errors()->first(), 'error');
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();
        $periodeId = (int) $validated['periode_id'];

        $rows = collect($validated['entries'])
            ->map(fn($e)=>['nim'=>trim((string)($e['nim'] ?? '')),'kelas_id'=>(int) ($e['kelas_id'] ?? 0)])
            ->filter(fn($r)=>$r['nim'] && $r['kelas_id'])
            ->values();

        if ($rows->isEmpty()) {
            Alert::toast('Minimal satu anggota diperlukan.', 'error');
            return back()->withErrors(['entries'=>'Minimal satu anggota diperlukan.'])->withInput();
        }

        $nimList   = $rows->pluck('nim')->unique()->values();
        $mahasiswa = Mahasiswa::whereIn('nim', $nimList)->get(['id','nim']);

        $missing = $nimList->diff($mahasiswa->pluck('nim'))->values();
        if ($missing->isNotEmpty()) {
            Alert::toast('NIM tidak ditemukan: '.implode(', ', $missing->all()), 'error');
            return back()->withErrors(['entries'=>'NIM tidak ditemukan: '.implode(', ', $missing->all())])->withInput();
        }

        if (!empty($validated['ketua_nim']) && !$nimList->contains($validated['ketua_nim'])) {
            Alert::toast('NIM Ketua harus salah satu dari daftar anggota.', 'error');
            return back()->withErrors(['ketua_nim'=>'NIM Ketua harus salah satu dari daftar anggota.'])->withInput();
        }

        // Cegah yang sudah terdaftar di periode ini
        $sudah = [];
        foreach ($mahasiswa as $m) {
            if ($m->kelompoks()->wherePivot('periode_id',$periodeId)->exists()) $sudah[] = $m->nim;
        }
        if ($sudah) {
            Alert::toast('NIM sudah terdaftar pada periode ini: '.implode(', ', $sudah), 'error');
            return back()->withErrors(['entries'=>'NIM sudah terdaftar pada periode ini: '.implode(', ', $sudah)])->withInput();
        }

        // siapkan pivot
        $ketuaNim = $validated['ketua_nim'] ?? null;
        $nimToId  = $mahasiswa->pluck('id','nim');
        $attach   = [];
        foreach ($rows as $r) {
            $mid = $nimToId[$r['nim']];
            $attach[$mid] = [
                'periode_id' => $periodeId,
                'kelas_id'   => $r['kelas_id'],
                'role'       => ($ketuaNim && $r['nim']===$ketuaNim) ? 'Ketua' : 'Anggota',
            ];
        }
        if (!$ketuaNim && $rows->count()) {
            $firstNim = $rows->first()['nim'];
            $firstId  = $nimToId[$firstNim];
            if (isset($attach[$firstId])) $attach[$firstId]['role'] = 'Ketua';
        }

        // nama_kelompok: boleh isi manual; jika kosong → auto generate
        $namaKelompok = null;

        try {
            DB::transaction(function() use ($validated,$periodeId,$attach,&$namaKelompok) {

                // jika user MENGISI nama_kelompok → pakai itu
                if (!empty($validated['nama_kelompok'])) {
                    $nama = $validated['nama_kelompok'];
                    $kelompok = Kelompok::create([
                        'uuid'          => (string) Str::uuid(),
                        'periode_id'    => $periodeId,
                        'nama_kelompok' => $nama,
                        'link_drive'    => $validated['link_drive'] ?? null,
                    ]);
                    $kelompok->mahasiswas()->attach($attach);
                    $namaKelompok = $nama;
                    return;
                }

                // kalau KOSONG → auto-generate unik per-periode (dengan retry)
                $attempt = 0; $maxAttempt = 25;
                do {
                    $attempt++;
                    $n = Kelompok::where('periode_id',$periodeId)->count() + $attempt;
                    $candidate = 'Kelompok '.$n;

                    try {
                        $kelompok = Kelompok::create([
                            'uuid'          => (string) Str::uuid(),
                            'periode_id'    => $periodeId,
                            'nama_kelompok' => $candidate,
                            'link_drive'    => $validated['link_drive'] ?? null,
                        ]);
                        $kelompok->mahasiswas()->attach($attach);
                        $namaKelompok = $candidate;
                        break;
                    } catch (\Illuminate\Database\QueryException $e) {
                        $msg = strtolower($e->getMessage());
                        $isUnique = str_contains($msg,'duplicate') || str_contains($msg,'unique');
                        if (!$isUnique || $attempt >= $maxAttempt) { throw $e; }
                        // retry next number
                    }
                } while (true);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // khusus kalau user isi manual dan bentrok unik
            Alert::toast('Nama kelompok sudah dipakai pada periode ini.', 'error');
            return back()->withErrors(['nama_kelompok'=>'Nama kelompok sudah dipakai pada periode ini.'])->withInput();
        }

        Alert::toast('Kelompok "'.$namaKelompok.'" berhasil ditambahkan.', 'success');
        return redirect()->route('kelompok.index');
    }

    public function show(string $uuid)
    {
        $kelompok = Kelompok::where('uuid',$uuid)
            ->with(['periode','mahasiswas'])
            ->firstOrFail();

        $kelasMap = Kelas::pluck('kelas','id');

        return view('admin.kelompok.show', compact('kelompok','kelasMap'));
    }

    public function edit(Request $request, string $uuid)
    {
        $kelompok = Kelompok::where('uuid',$uuid)
            ->with(['periode','mahasiswas'])
            ->firstOrFail();

        $periodes  = Periode::orderByDesc('id')->get(['id','periode']);
        $kelasList = Kelas::orderBy('kelas')->get(['id','kelas']);

        // Periode yang sedang dipakai (atau old input)
        $selectedPeriodeId = (int) old('periode_id', $kelompok->periode_id);

        // Mahasiswa yang sudah terpakai di periode ini, KECUALI yang ada di kelompok ini
        $takenExceptThis = DB::table('kelompok_mahasiswa')
            ->where('periode_id', $selectedPeriodeId)
            ->where('kelompok_id', '<>', $kelompok->id)
            ->pluck('mahasiswa_id');

        // Tampilkan semua yang belum terpakai + anggota kelompok ini sendiri
        $mahasiswas = Mahasiswa::whereNotIn('id', $takenExceptThis)
            ->orderBy('nama_mahasiswa')
            ->get(['id','nim','nama_mahasiswa']);

        return view('admin.kelompok.edit', compact('kelompok','periodes','kelasList','mahasiswas','selectedPeriodeId'));
    }

    public function update(Request $request, string $uuid)
    {
        $kelompok = Kelompok::where('uuid',$uuid)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'periode_id'          => ['required','integer','exists:periode,id'],
            'nama_kelompok'       => [
                'required','string','max:100',
                Rule::unique('kelompok','nama_kelompok')
                    ->where(fn($q)=>$q->where('periode_id',$request->periode_id))
                    ->ignore($kelompok->id),
            ],
            'link_drive'          => ['nullable','url','max:255'],
            'ketua_nim'           => ['nullable','string','max:50'],
            'entries'             => ['required','array','min:1'],
            'entries.*.nim'       => ['required','string','max:50','distinct'],
            'entries.*.kelas_id'  => ['required','integer','exists:kelas,id'],
        ], [
            'entries.required'       => 'Minimal satu anggota diperlukan.',
            'entries.*.nim.distinct' => 'Ada NIM yang dobel pada daftar anggota.',
            'nama_kelompok.unique'   => 'Nama kelompok sudah ada pada periode ini.',
        ]);

        if ($validator->fails()) {
            Alert::toast($validator->errors()->first(), 'error');
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();
        $periodeId = (int) $validated['periode_id'];

        $rows = collect($validated['entries'])
            ->map(fn($e)=>['nim'=>trim((string)($e['nim'] ?? '')),'kelas_id'=>(int) ($e['kelas_id'] ?? 0)])
            ->filter(fn($r)=>$r['nim'] && $r['kelas_id'])
            ->values();

        if ($rows->isEmpty()) {
            Alert::toast('Minimal satu anggota diperlukan.', 'error');
            return back()->withErrors(['entries'=>'Minimal satu anggota diperlukan.'])->withInput();
        }

        $nimList   = $rows->pluck('nim')->unique()->values();
        $mahasiswa = Mahasiswa::whereIn('nim',$nimList)->get(['id','nim']);

        $missing = $nimList->diff($mahasiswa->pluck('nim'))->values();
        if ($missing->isNotEmpty()) {
            Alert::toast('NIM tidak ditemukan: '.implode(', ', $missing->all()), 'error');
            return back()->withErrors(['entries'=>'NIM tidak ditemukan: '.implode(', ', $missing->all())])->withInput();
        }

        if (!empty($validated['ketua_nim']) && !$nimList->contains($validated['ketua_nim'])) {
            Alert::toast('NIM Ketua harus salah satu dari daftar anggota.', 'error');
            return back()->withErrors(['ketua_nim'=>'NIM Ketua harus salah satu dari daftar anggota.'])->withInput();
        }

        // Cek konflik: terdaftar di kelompok lain dalam periode yang sama
        $konflik = [];
        foreach ($mahasiswa as $m) {
            $existsLain = $m->kelompoks()
                ->wherePivot('periode_id',$periodeId)
                ->where('kelompok.id','<>',$kelompok->id)
                ->exists();
            if ($existsLain) $konflik[] = $m->nim;
        }
        if ($konflik) {
            Alert::toast('NIM sudah terdaftar pada kelompok lain di periode ini: '.implode(', ', $konflik), 'error');
            return back()->withErrors(['entries'=>'NIM sudah terdaftar pada kelompok lain di periode ini: '.implode(', ', $konflik)])->withInput();
        }

        DB::transaction(function() use ($kelompok,$validated,$mahasiswa,$rows,$periodeId) {
            $kelompok->update([
                'periode_id'    => $periodeId,
                'nama_kelompok' => $validated['nama_kelompok'],
                'link_drive'    => $validated['link_drive'] ?? null,
            ]);

            $ketuaNim = $validated['ketua_nim'] ?? null;
            $nimToId  = $mahasiswa->pluck('id','nim');

            $attach = [];
            foreach ($rows as $r) {
                $mid = $nimToId[$r['nim']];
                $attach[$mid] = [
                    'periode_id' => $periodeId,
                    'kelas_id'   => $r['kelas_id'],
                    'role'       => ($ketuaNim && $r['nim']===$ketuaNim) ? 'Ketua' : 'Anggota',
                ];
            }
            if (!$ketuaNim && $rows->count()) {
                $firstNim = $rows->first()['nim'];
                $firstId  = $nimToId[$firstNim];
                if (isset($attach[$firstId])) $attach[$firstId]['role'] = 'Ketua';
            }

            $kelompok->mahasiswas()->sync($attach);
        });

        Alert::toast('Kelompok berhasil diubah.', 'success');
        return redirect()->route('kelompok.index');
    }

    public function destroy(string $uuid)
    {
        $kelompok = Kelompok::where('uuid',$uuid)->firstOrFail();
        $kelompok->delete();

        Alert::toast('Kelompok berhasil dihapus.', 'success');
        return redirect()->route('kelompok.index');
    }
}
