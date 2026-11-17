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
        ], [
            'nip.unique' => 'Dosen dengan NIP ini sudah terdaftar dalam Bank Data Dosen.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain. Silakan periksa daftar pengguna.',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $nip = $validated['nip'] ?? null;
            $nama = $validated['nama_dosen'];
            $noHp = $validated['no_hp'] ?? null;
            $keahlian = $validated['keahlian'] ?? null;
            $email = $validated['email'] ?? null;

            $dosen = Dosen::create([
                'nip' => $nip,
                'nama_dosen' => $nama,
                'no_hp' => $noHp,
                'keahlian' => $keahlian,
            ]);

            // Jika create_user bernilai true atau tidak ada parameter create_user (default auto-generate akun)
            $shouldCreateUser = $request->has('create_user') ? $request->boolean('create_user') : true;

            if ($shouldCreateUser) {
                $this->createOrLinkUserForDosen($dosen, $email);
            }
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
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($dosen->user_id)],
            'create_user' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($dosen, $validated, $request) {
            $user = $dosen->user;
            $newNip = $validated['nip'] ?? null;
            $nama = $validated['nama_dosen'];
            $noHp = $validated['no_hp'] ?? null;

            if ($user) {
                $user->nama_user = $nama;
                if (! empty($noHp)) {
                    $user->no_hp = $noHp;
                }
                if ($newNip && $dosen->nip && $user->username === $dosen->nip && ! User::where('username', $newNip)->where('id', '!=', $user->id)->exists()) {
                    $user->username = $newNip;
                }
                $user->save();
            } elseif ($request->boolean('create_user')) {
                // Buat akun baru otomatis jika belum terhubung
                $this->createOrLinkUserForDosen($dosen, $validated['email'] ?? null);
            }

            $dosen->update([
                'nip' => $newNip,
                'nama_dosen' => $nama,
                'no_hp' => $noHp,
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
     * Sinkronkan akun user bertipe dosen ke Bank Data Dosen sekaligus generate akun untuk dosen yang belum memiliki akun.
     */
    public function sync(): RedirectResponse
    {
        $created = 0;
        $attached = 0;
        $skipped = 0;
        $generated = 0;

        $users = User::where('role', 'dosen')->get();

        DB::transaction(function () use ($users, &$created, &$attached, &$skipped, &$generated) {
            // 1. Tautkan akun user dosen ke bank data dosen
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

            // 2. Generate akun otomatis untuk dosen yang belum memiliki akun
            $unlinkedDosens = Dosen::whereNull('user_id')->get();
            foreach ($unlinkedDosens as $unlinked) {
                $this->createOrLinkUserForDosen($unlinked);
                $generated++;
            }
        });

        $msg = "Sinkronisasi selesai: dibuat: {$created}, ditautkan: {$attached}, akun digenerate: {$generated}, dilewati: {$skipped}.";
        Alert::toast($msg, 'success');

        return redirect()->route('dosen.index');
    }

    /**
     * Generate akun login pengguna dengan password 123456 secara massal untuk semua dosen yang belum memiliki akun.
     */
    public function generateAccounts(): RedirectResponse
    {
        $generated = 0;
        $dosenList = Dosen::whereNull('user_id')->get();

        DB::transaction(function () use ($dosenList, &$generated) {
            foreach ($dosenList as $dosen) {
                $this->createOrLinkUserForDosen($dosen);
                $generated++;
            }
        });

        if ($generated > 0) {
            Alert::toast("Berhasil membuat {$generated} akun dosen baru secara otomatis dengan password default: 123456", 'success');
        } else {
            Alert::toast('Semua dosen dalam Bank Data sudah memiliki akun pengguna aktif.', 'info');
        }

        return redirect()->route('dosen.index');
    }

    /**
     * Generate akun login pengguna dengan password 123456 untuk satu data dosen tertentu.
     */
    public function generateAccount(Dosen $dosen): RedirectResponse
    {
        if ($dosen->user_id && $dosen->user) {
            Alert::info('Akun Sudah Ada', "Dosen {$dosen->nama_dosen} sudah memiliki akun login ({$dosen->user->username}).");

            return redirect()->route('dosen.index');
        }

        DB::transaction(function () use ($dosen) {
            $this->createOrLinkUserForDosen($dosen);
        });

        Alert::toast("Akun dosen {$dosen->nama_dosen} berhasil dibuat dengan password default: 123456", 'success');

        return redirect()->route('dosen.index');
    }

    /**
     * Buat akun User baru dengan password default '123456' untuk model Dosen, atau tautkan jika akun sudah ada.
     */
    public function createOrLinkUserForDosen(Dosen $dosen, ?string $email = null): User
    {
        // 1. Coba cari user yang sudah ada berdasarkan NIP
        $user = null;
        if (! empty($dosen->nip)) {
            $user = User::where('username', $dosen->nip)->first();
        }

        // 2. Jika belum ketemu, cari berdasarkan email (jika disediakan)
        if (! $user && ! empty($email)) {
            $user = User::where('email', $email)->first();
        }

        // 3. Jika belum ketemu, cari berdasarkan kesamaan nama dengan role dosen/evaluator
        if (! $user) {
            $user = User::whereIn('role', ['dosen', 'evaluator'])
                ->whereRaw('LOWER(nama_user) = ?', [strtolower(trim($dosen->nama_dosen))])
                ->whereDoesntHave('dosen')
                ->first();
        }

        if ($user) {
            if ($dosen->user_id !== $user->id) {
                $dosen->user_id = $user->id;
                $dosen->save();
            }

            return $user;
        }

        // Buat User baru otomatis dengan password default '123456'
        $username = ! empty($dosen->nip)
            ? $dosen->nip
            : ('dosen_'.Str::slug(explode(' ', trim($dosen->nama_dosen))[0]).rand(100, 999));

        if (User::where('username', $username)->exists()) {
            $username = $username.'_'.rand(10, 99);
        }

        if (empty($email)) {
            $email = strtolower(($dosen->nip ?: $username).'@politala.ac.id');
            if (User::where('email', $email)->exists()) {
                $email = strtolower(($dosen->nip ?: $username).'+'.Str::random(4).'@politala.ac.id');
            }
        }

        $user = User::create([
            'nama_user' => $dosen->nama_dosen,
            'email' => $email,
            'email_verified_at' => now(),
            'no_hp' => $dosen->no_hp ?: '-',
            'username' => $username,
            'password' => Hash::make('123456'),
            'role' => 'dosen',
            'remember_token' => Str::random(10),
        ]);

        $dosen->user_id = $user->id;
        $dosen->save();

        return $user;
    }

    /**
     * Tampilkan formulir impor data dosen.
     */
    public function importForm(): View
    {
        return view('admin.dosen.import');
    }

    /**
     * Proses unggah berkas impor data dosen.
     */
    public function import(Request $request, \App\Services\DosenImportService $importService): RedirectResponse
    {
        $request->validate([
            'file' => [
                'required', 'file',
                'mimes:xlsx,xls,csv,txt',
                'max:5120',
            ],
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $ext = $file->getClientOriginalExtension();

        try {
            $result = $importService->importFile($path, $ext);
        } catch (\Exception $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }

        $stats = $result['stats'];
        $errors = $result['errors'];

        session()->flash('import_stats', $stats);

        if (! empty($errors)) {
            session()->flash('import_errors', $errors);
            Alert::toast("Import selesai: dibuat: {$stats['created_dosen']}, diperbarui: {$stats['updated_dosen']}, ditautkan: {$stats['attached']} (ada error)", 'warning');
        } else {
            Alert::toast("Import selesai: dibuat: {$stats['created_dosen']}, diperbarui: {$stats['updated_dosen']}, ditautkan: {$stats['attached']}", 'success');
        }

        return redirect()->route('dosen.import.form');
    }

    /**
     * Unduh template impor Excel (.xlsx) untuk Dosen.
     */
    public function templateExcel()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\DosenTemplateExport, 'template-dosen.xlsx');
    }

    /**
     * Unduh template impor CSV untuk Dosen.
     */
    public function templateCsv()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\DosenTemplateExport, 'template-dosen.csv', \Maatwebsite\Excel\Excel::CSV);
    }
}
