<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use RealRashid\SweetAlert\Facades\Alert;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = User::latest()->get();

        return view('admin.user.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.user.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $isMahasiswa = $request->input('role') === 'mahasiswa';
        $username = $isMahasiswa
            ? strtoupper(preg_replace('/\s+/', '', (string) $request->username))
            : strtolower(trim((string) $request->username));

        // Normalisasi ringan
        $request->merge([
            'nama_user' => preg_replace('/\s+/', ' ', trim((string) $request->nama_user)),
            'email' => strtolower(trim((string) $request->email)),
            'username' => $username,
            'no_hp' => preg_replace('/\s+/', '', trim((string) $request->no_hp)),
        ]);

        $validated = $request->validate([
            'nama_user' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'no_hp' => 'required|string|max:30',
            'username' => 'required|string|max:50|alpha_dash|unique:users,username', // <- unique
            'password' => 'nullable|string|min:5|confirmed', // kosong = pakai default
            'role' => 'required|in:admin,dosen,evaluator,mahasiswa',
        ], [
            'email.unique' => 'Email ini sudah terdaftar pada akun lain. Silakan periksa daftar pengguna untuk mengedit akun yang sudah ada.',
            'username.unique' => 'Username ini sudah terdaftar pada akun lain. Silakan periksa daftar pengguna untuk mengedit akun yang sudah ada.',
        ]);

        // Pencegahan akun ganda: jika role mahasiswa, periksa apakah NIM sudah terhubung ke akun lain
        if ($validated['role'] === 'mahasiswa') {
            $nim = strtoupper($validated['username']);
            $existingMhs = \App\Models\Mahasiswa::where('nim', $nim)->first();

            if ($existingMhs && $existingMhs->user_id) {
                $linkedUser = User::find($existingMhs->user_id);
                Alert::warning(
                    'Akun Sudah Ada',
                    "Mahasiswa dengan NIM {$nim} sudah memiliki akun pengguna aktif (Username: {$linkedUser?->username}). Anda dapat mengedit akun yang sudah ada."
                );

                return back()->withInput()->withErrors([
                    'username' => "Mahasiswa dengan NIM {$nim} sudah memiliki akun pengguna aktif ({$linkedUser?->username}).",
                ]);
            }
        }

        $payload = [
            'nama_user' => $validated['nama_user'],
            'email' => $validated['email'],
            'email_verified_at' => now(),
            'no_hp' => $validated['no_hp'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password'] ?? '123456'), // default jika kosong
            'role' => $validated['role'],
            'remember_token' => Str::random(10),
        ];

        $user = User::create($payload);

        // Jika role mahasiswa, sesuaikan dengan Bank Data Mahasiswa
        if ($validated['role'] === 'mahasiswa') {
            $nim = strtoupper($validated['username']);
            $m = \App\Models\Mahasiswa::where('nim', $nim)->first();
            if ($m) {
                $m->user_id = $user->id;
                $m->nama_mahasiswa = $validated['nama_user'];
                $m->save();
            } else {
                \App\Models\Mahasiswa::create([
                    'user_id' => $user->id,
                    'nim' => $nim,
                    'nama_mahasiswa' => $validated['nama_user'],
                    'semester' => 1,
                ]);
            }
        }

        Alert::toast('Pengguna berhasil ditambahkan.', 'success');

        return redirect()->route('user.index');
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
    public function edit(User $user)
    {
        return view('admin.user.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        // $user resolved by UUID (route model binding)

        // Normalisasi hanya jika field dikirim
        if ($request->has('nama_user')) {
            $request->merge(['nama_user' => preg_replace('/\s+/', ' ', trim($request->nama_user))]);
        }
        if ($request->has('email')) {
            $request->merge(['email' => strtolower(trim($request->email))]);
        }
        if ($request->has('username')) {
            $request->merge(['username' => strtolower(trim($request->username))]);
        }
        if ($request->has('no_hp')) {
            $request->merge(['no_hp' => preg_replace('/\s+/', '', trim($request->no_hp))]);
        }

        $validated = $request->validate([
            'nama_user' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'no_hp' => 'required|string|max:30',
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)], // <- unique
            'password' => 'nullable|string|min:5|confirmed',
            'role' => 'required|in:admin,dosen,evaluator,mahasiswa',
        ]);

        $updateData = [
            'nama_user' => $validated['nama_user'],
            'email' => $validated['email'],
            'no_hp' => $validated['no_hp'],
            'username' => $validated['username'],
            'role' => $validated['role'],
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        // Jika user adalah mahasiswa, sesuaikan nama di Bank Data Mahasiswa
        if ($user->role === 'mahasiswa') {
            $m = $user->getOrResolveMahasiswa();
            if ($m) {
                $newNim = strtoupper($user->username);
                if ($newNim !== $m->nim) {
                    $unlinkedDuplicate = \App\Models\Mahasiswa::where('nim', $newNim)
                        ->where('id', '!=', $m->id)
                        ->whereNull('user_id')
                        ->first();
                    if ($unlinkedDuplicate) {
                        \Illuminate\Support\Facades\DB::table('kelompok_mahasiswa')
                            ->where('mahasiswa_id', $unlinkedDuplicate->id)
                            ->update(['mahasiswa_id' => $m->id]);
                        $unlinkedDuplicate->delete();
                    }
                    $m->nim = $newNim;
                }
                $m->nama_mahasiswa = $user->nama_user;
                $m->save();
            }
        }

        Alert::toast('Pengguna berhasil diperbarui.', 'success');

        return redirect()->route('user.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        Alert::toast('Pengguna berhasil dihapus.', 'success');

        return redirect()->route('user.index');
    }

    public function resetPassword(User $user)
    {
        $newPassword = $user->username.'@pbl';

        $user->update(['password' => $newPassword]);

        Alert::toast("Password {$user->nama_user} berhasil direset menjadi: {$newPassword}", 'success');

        return back();
    }
}
