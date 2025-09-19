<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;    
use Illuminate\Validation\Rule;
use RealRashid\SweetAlert\Facades\Alert;
use App\Http\Controllers\Controller;

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
     // Normalisasi ringan
    $request->merge([
        'nama_user' => preg_replace('/\s+/', ' ', trim($request->nama_user)),
        'email'     => strtolower(trim($request->email)),
        'username'  => strtolower(trim($request->username)),
        'no_hp'     => preg_replace('/\s+/', '', trim($request->no_hp)),
    ]);

    $validated = $request->validate([
        'nama_user' => 'required|string|max:255',
        'email'     => 'required|email|max:255|unique:users,email',
        'no_hp'     => 'required|string|max:30',
        'username'  => 'required|string|max:50|alpha_dash|unique:users,username', // <- unique
        'password'  => 'nullable|string|min:5|confirmed', // kosong = pakai default
        'role'      => 'required|in:admin,evaluator,mahasiswa',
    ]);

    $payload = [
        'nama_user'         => $validated['nama_user'],
        'email'             => $validated['email'],
        'email_verified_at' => now(),
        'no_hp'             => $validated['no_hp'],
        'username'          => $validated['username'],
        'password'          => Hash::make($validated['password'] ?? '123456'), // default jika kosong
        'role'              => $validated['role'],
        'remember_token'    => Str::random(10),
    ];

    User::create($payload);
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
        if ($request->has('nama_user')) $request->merge(['nama_user' => preg_replace('/\s+/', ' ', trim($request->nama_user))]);
        if ($request->has('email'))     $request->merge(['email'     => strtolower(trim($request->email))]);
        if ($request->has('username'))  $request->merge(['username'  => strtolower(trim($request->username))]);
        if ($request->has('no_hp'))     $request->merge(['no_hp'     => preg_replace('/\s+/', '', trim($request->no_hp))]);

        $validated = $request->validate([
            'nama_user' => 'required|string|max:255',
            'email'     => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'no_hp'     => 'required|string|max:30',
            'username'  => ['required','string','max:50','alpha_dash', Rule::unique('users','username')->ignore($user->id)], // <- unique
            'password'  => 'nullable|string|min:5|confirmed',
            'role'      => 'required|in:admin,evaluator,mahasiswa',
        ]);

        $updateData = [
            'nama_user' => $validated['nama_user'],
            'email'     => $validated['email'],
            'no_hp'     => $validated['no_hp'],
            'username'  => $validated['username'],
            'role'      => $validated['role'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);
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
