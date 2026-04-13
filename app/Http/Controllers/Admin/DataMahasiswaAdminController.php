<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use RealRashid\SweetAlert\Facades\Alert;

class DataMahasiswaAdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Mahasiswa::with('user')->latest()->get();

        return view('admin.mahasiswa.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.mahasiswa.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Normalisasi
        $request->merge([
            'nim' => strtoupper(preg_replace('/\s+/', '', $request->nim)),
            'nama_mahasiswa' => preg_replace('/\s+/', ' ', trim($request->nama_mahasiswa)),
        ]);

        $validated = $request->validate([
            'nim' => [
                'required', 'string', 'max:50',
                Rule::unique('mahasiswa', 'nim'),
                Rule::unique('users', 'username'),
            ],
            'nama_mahasiswa' => ['required', 'string', 'max:255'],
            'kelas_id' => ['nullable', 'integer', 'exists:kelas,id'],
        ]);

        DB::transaction(function () use ($validated) {
            $nim = $validated['nim'];
            $name = $validated['nama_mahasiswa'];

            // buat email otomatis (ubah domain sesuai kebutuhan)
            $email = strtolower($nim.'@politala.ac.id');
            if (User::where('email', $email)->exists()) {
                $email = strtolower($nim.'+'.Str::random(4).'@politala.ac.id');
            }

            $user = User::create([
                'nama_user' => $name,
                'email' => $email,
                'email_verified_at' => now(),
                'no_hp' => '-',              // placeholder
                'username' => $nim,            // username = NIM
                'password' => Hash::make($nim), // password = NIM
                'role' => 'mahasiswa',
                'remember_token' => Str::random(10),
            ]);

            Mahasiswa::create([
                // 'uuid' otomatis diisi di model (booted creating)
                'user_id' => $user->id,
                'nim' => $nim,
                'nama_mahasiswa' => $name,
                'kelas_id' => $validated['kelas_id'] ?? null,
            ]);
        });

        Alert::toast('Mahasiswa & akun Pengguna berhasil ditambahkan.', 'success');

        return redirect()->route('mahasiswa.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Mahasiswa $mahasiswa)
    {
        $mahasiswa->load('user');

        return view('admin.mahasiswa.edit', compact('mahasiswa'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Mahasiswa $mahasiswa)
    {
        if ($request->has('nim')) {
            $request->merge(['nim' => strtoupper(preg_replace('/\s+/', '', $request->nim))]);
        }
        if ($request->has('nama_mahasiswa')) {
            $request->merge(['nama_mahasiswa' => preg_replace('/\s+/', ' ', trim($request->nama_mahasiswa))]);
        }

        $validated = $request->validate([
            'nim' => [
                'sometimes', 'required', 'string', 'max:50',
                Rule::unique('mahasiswa', 'nim')->ignore($mahasiswa->id),       // ignore by PK id
                Rule::unique('users', 'username')->ignore($mahasiswa->user_id), // ignore user pemilik
            ],
            'nama_mahasiswa' => ['required', 'string', 'max:255'],
            'kelas_id' => ['nullable', 'integer', 'exists:kelas,id'],
        ]);

        DB::transaction(function () use ($mahasiswa, $validated) {
            $user = $mahasiswa->user;

            // sinkron nama
            $user->nama_user = $validated['nama_mahasiswa'];

            // jika NIM berubah: sync username & password user
            if (! empty($validated['nim']) && $validated['nim'] !== $mahasiswa->nim) {
                $newNim = $validated['nim'];
                $user->username = $newNim;
                $user->password = Hash::make($newNim); // password mengikuti NIM
                $mahasiswa->nim = $newNim;
                // (opsional) juga ubah email ke pola $newNim@domain
                // $user->email = strtolower($newNim.'@politala.ac.id');
            }

            $mahasiswa->nama_mahasiswa = $validated['nama_mahasiswa'];

            // Update kelas jika ada
            if (array_key_exists('kelas_id', $validated)) {
                $mahasiswa->kelas_id = $validated['kelas_id'];
            }

            $user->save();
            $mahasiswa->save();
        });

        Alert::toast('Data mahasiswa berhasil diperbarui & disinkronkan.', 'success');

        return redirect()->route('mahasiswa.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Mahasiswa $mahasiswa)
    {
        DB::transaction(function () use ($mahasiswa) {
            // hapus akun user agar tidak orphan
            $mahasiswa->user()->delete();
            $mahasiswa->delete();
        });

        Alert::toast('Mahasiswa & akun user terkait berhasil dihapus.', 'success');

        return redirect()->route('mahasiswa.index');
    }

    /**
     * Sinkronkan user.role=mahasiswa ke tabel mahasiswa:
     * - Buat record mahasiswa jika belum ada (by user_id/username=NIM)
     * - Tautkan jika ada record by NIM
     * - Perbarui nama/NIM jika berubah
     * - Catat konflik jika NIM bentrok
     */
    public function sync()
    {
        $created = 0;   // dibuat baru
        $updated = 0;   // diperbarui (nim/nama)
        $attached = 0;  // ditautkan (find by NIM, lalu isi user_id)
        $skipped = 0;   // tidak berubah
        $conflict = 0;  // konflik (NIM dipakai entri lain)

        $users = User::where('role', 'mahasiswa')->get();

        DB::transaction(function () use ($users, &$created, &$updated, &$attached, &$skipped, &$conflict) {
            foreach ($users as $user) {
                $nim = strtoupper(preg_replace('/\s+/', '', (string) $user->username));
                $nama = preg_replace('/\s+/', ' ', trim((string) $user->nama_user));

                // Sudah ada record mahasiswa untuk user ini?
                $m = Mahasiswa::where('user_id', $user->id)->first();
                if ($m) {
                    $changed = false;

                    if ($m->nama_mahasiswa !== $nama) {
                        $m->nama_mahasiswa = $nama;
                        $changed = true;
                    }
                    if ($nim && $m->nim !== $nim) {
                        $dupe = Mahasiswa::where('nim', $nim)->where('id', '!=', $m->id)->exists();
                        if ($dupe) {
                            $conflict++;
                        } else {
                            $m->nim = $nim;
                            $changed = true;
                        }
                    }

                    if ($changed) {
                        $m->save();
                        $updated++;
                    } else {
                        $skipped++;
                    }

                    continue;
                }

                // Belum ada by user_id → coba cari berdasarkan NIM
                $mByNim = $nim ? Mahasiswa::where('nim', $nim)->first() : null;
                if ($mByNim) {
                    if ($mByNim->user_id && $mByNim->user_id !== $user->id) {
                        $conflict++;
                    } else {
                        $mByNim->user_id = $user->id;
                        if ($mByNim->nama_mahasiswa !== $nama) {
                            $mByNim->nama_mahasiswa = $nama;
                        }
                        $mByNim->save();
                        $attached++;
                    }

                    continue;
                }

                // Tidak ada -> buat baru
                Mahasiswa::create([
                    // uuid auto dalam model
                    'user_id' => $user->id,
                    'nim' => $nim ?: (string) $user->username,
                    'nama_mahasiswa' => $nama,
                ]);
                $created++;
            }
        });

        $msg = "Sinkronisasi selesai — dibuat: {$created}, diperbarui: {$updated}, ditautkan: {$attached}, dilewati: {$skipped}, konflik: {$conflict}";
        Alert::toast($msg, $conflict ? 'warning' : 'success');

        return redirect()->route('mahasiswa.index');
    }

    public function importForm()
    {
        return view('admin.mahasiswa.import');
    }

    public function import(\Illuminate\Http\Request $request, \App\Services\MahasiswaImportService $importService)
    {
        set_time_limit(300);

        $request->validate([
            'file' => [
                'required', 'file',
                'mimetypes:text/plain,text/csv,application/vnd.ms-excel',
                'max:2048',
            ],
        ]);

        $path = $request->file('file')->getRealPath();
        
        try {
            $result = $importService->importFromCsv($path);
        } catch (\Exception $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }

        $stats = $result['stats'];
        $errors = $result['errors'];

        session()->flash('import_stats', $stats);

        if ($errors) {
            session()->flash('import_errors', $errors);
            \RealRashid\SweetAlert\Facades\Alert::toast("Import selesai — user baru: {$stats['created_users']}, mhs baru: {$stats['created_mhs']}, diperbarui: {$stats['updated_mhs']}, ditautkan: {$stats['attached']}, dilewati: {$stats['skipped']}, konflik: {$stats['conflict']} (ada error)", 'warning');
        } else {
            \RealRashid\SweetAlert\Facades\Alert::toast("Import selesai — user baru: {$stats['created_users']}, mhs baru: {$stats['created_mhs']}, diperbarui: {$stats['updated_mhs']}, ditautkan: {$stats['attached']}, dilewati: {$stats['skipped']}, konflik: {$stats['conflict']}", 'success');
        }

        return redirect()->route('mahasiswa.import.form');
    }
}

