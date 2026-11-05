<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;

class DataDosenAdminController extends Controller
{
    /**
     * Tampilkan daftar master Bank Data Dosen.
     */
    public function index(): View
    {
        $data = Dosen::with('user')->latest()->get();

        return view('admin.dosen.index', compact('data'));
    }

    /**
     * Form tambah dosen baru ke Bank Data.
     */
    public function create(): View
    {
        return view('admin.dosen.create');
    }

    /**
     * Simpan data dosen baru ke Bank Data.
     */
    public function store(Request $request): RedirectResponse
    {
        // Normalisasi
        if ($request->filled('nip')) {
            $request->merge(['nip' => strtoupper(preg_replace('/\s+/', '', (string) $request->nip))]);
        }
        if ($request->filled('nama_dosen')) {
            $request->merge(['nama_dosen' => preg_replace('/\s+/', ' ', trim((string) $request->nama_dosen))]);
        }

        $validated = $request->validate([
            'nip' => ['nullable', 'string', 'max:50', Rule::unique('dosen', 'nip')],
            'nama_dosen' => ['required', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:50'],
            'keahlian' => ['nullable', 'string', 'max:1000'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')],
            'create_user' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $nip = $validated['nip'] ?? null;
            $nama = $validated['nama_dosen'];
            $noHp = $validated['no_hp'] ?? null;
            $keahlian = $validated['keahlian'] ?? null;
            $email = $validated['email'] ?? null;

            $user = null;

            // Jika NIP diisi, periksa apakah akun user sudah ada
            if ($nip) {
                $user = User::where('username', $nip)->first();
            }

            // Jika user belum ada dan opsi buat akun dicentang
            if (! $user && $request->boolean('create_user')) {
                $username = $nip ?: ('dosen_'.Str::slug(explode(' ', $nama)[0]).rand(100, 999));

                if (empty($email)) {
                    $email = strtolower(($nip ?: $username).'@politala.ac.id');
                    if (User::where('email', $email)->exists()) {
                        $email = strtolower(($nip ?: $username).'+'.Str::random(4).'@politala.ac.id');
                    }
                }

                $user = User::create([
                    'nama_user' => $nama,
                    'email' => $email,
                    'email_verified_at' => now(),
                    'no_hp' => $noHp ?: '-',
                    'username' => $username,
                    'password' => Hash::make($nip ?: 'dosen123'),
                    'role' => 'dosen',
                    'remember_token' => Str::random(10),
                ]);
            } elseif ($user && $user->nama_user !== $nama) {
                $user->nama_user = $nama;
                if ($noHp && (empty($user->no_hp) || $user->no_hp === '-')) {
                    $user->no_hp = $noHp;
                }
                $user->save();
            }

            Dosen::create([
                'user_id' => $user?->id,
                'nip' => $nip,
                'nama_dosen' => $nama,
                'no_hp' => $noHp,
                'keahlian' => $keahlian,
            ]);
        });

        Alert::toast('Data dosen berhasil ditambahkan ke Bank Data.', 'success');

        return redirect()->route('dosen.index');
    }

    /**
     * Form edit data dosen.
     */
    public function edit(Dosen $dosen): View
    {
        $dosen->load('user');

        return view('admin.dosen.edit', compact('dosen'));
    }

    /**
     * Perbarui data dosen di Bank Data.
     */
    public function update(Request $request, Dosen $dosen): RedirectResponse
    {
        if ($request->filled('nip')) {
            $request->merge(['nip' => strtoupper(preg_replace('/\s+/', '', (string) $request->nip))]);
        }
        if ($request->filled('nama_dosen')) {
            $request->merge(['nama_dosen' => preg_replace('/\s+/', ' ', trim((string) $request->nama_dosen))]);
        }

        $validated = $request->validate([
            'nip' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('dosen', 'nip')->ignore($dosen->id),
            ],
            'nama_dosen' => ['required', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:50'],
            'keahlian' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($dosen, $validated) {
            $user = $dosen->user;

            if ($user) {
                $user->nama_user = $validated['nama_dosen'];
                if (! empty($validated['no_hp'])) {
                    $user->no_hp = $validated['no_hp'];
                }
                $user->save();
            }

            $dosen->update([
                'nip' => $validated['nip'] ?? null,
                'nama_dosen' => $validated['nama_dosen'],
                'no_hp' => $validated['no_hp'] ?? null,
                'keahlian' => $validated['keahlian'] ?? null,
            ]);
        });

        Alert::toast('Data dosen berhasil diperbarui.', 'success');

        return redirect()->route('dosen.index');
    }

    /**
     * Hapus data dosen dari Bank Data.
     */
    public function destroy(Dosen $dosen): RedirectResponse
    {
        $dosen->delete();

        Alert::toast('Data dosen berhasil dihapus dari Bank Data.', 'success');

        return redirect()->route('dosen.index');
    }

    /**
     * Sinkronkan akun user bertipe dosen ke Bank Data Dosen.
     */
    public function sync(): RedirectResponse
    {
        $created = 0;
        $attached = 0;
        $skipped = 0;

        $users = User::where('role', 'dosen')->get();

        DB::transaction(function () use ($users, &$created, &$attached, &$skipped) {
            foreach ($users as $u) {
                $d = Dosen::where('user_id', $u->id)->first();

                if ($d) {
                    $changed = false;
                    if ($d->nama_dosen !== $u->nama_user) {
                        $d->nama_dosen = $u->nama_user;
                        $changed = true;
                    }
                    if (empty($d->no_hp) && ! empty($u->no_hp) && $u->no_hp !== '-') {
                        $d->no_hp = $u->no_hp;
                        $changed = true;
                    }
                    if ($changed) {
                        $d->save();
                    }
                    $skipped++;

                    continue;
                }

                // Coba cari record by NIP jika username terlihat seperti NIP
                $nip = preg_match('/^\d{8,}$/', (string) $u->username) ? $u->username : null;
                $dByNip = $nip ? Dosen::where('nip', $nip)->first() : null;

                if ($dByNip && ! $dByNip->user_id) {
                    $dByNip->user_id = $u->id;
                    $dByNip->save();
                    $attached++;
                } else {
                    Dosen::create([
                        'user_id' => $u->id,
                        'nip' => $nip,
                        'nama_dosen' => $u->nama_user,
                        'no_hp' => ($u->no_hp !== '-' ? $u->no_hp : null),
                        'keahlian' => null,
                    ]);
                    $created++;
                }
            }
        });

        Alert::toast("Sinkronisasi Dosen selesai: dibuat: {$created}, ditautkan: {$attached}, dilewati: {$skipped}.", 'success');

        return redirect()->route('dosen.index');
    }
}
