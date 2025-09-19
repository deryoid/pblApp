<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = $request->user();

        // Normalisasi
        if ($request->has('nama_user')) $request->merge(['nama_user' => preg_replace('/\s+/', ' ', trim($request->nama_user))]);
        if ($request->has('email'))     $request->merge(['email'     => strtolower(trim($request->email))]);
        if ($request->has('username'))  $request->merge(['username'  => strtolower(trim($request->username))]);
        if ($request->has('no_hp'))     $request->merge(['no_hp'     => preg_replace('/\s+/', '', trim($request->no_hp))]);

        $validated = $request->validate([
            'nama_user' => 'required|string|max:255',
            'email'     => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'no_hp'     => 'required|string|max:30',
            'username'  => ['required','string','max:50','alpha_dash', Rule::unique('users','username')->ignore($user->id)],
            'photo'     => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'remove_photo' => 'sometimes|boolean',
        ]);

        $user->nama_user = $validated['nama_user'];
        $user->email     = $validated['email'];
        $user->no_hp     = $validated['no_hp'];
        $user->username  = $validated['username'];

        // Handle photo blob
        if ($request->boolean('remove_photo')) {
            $user->profile_photo = null;
            $user->profile_photo_mime = null;
        }

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $user->profile_photo = file_get_contents($file->getRealPath());
            $user->profile_photo_mime = $file->getMimeType();
        }

        $user->save();

        return back()->with('status', 'Profil berhasil diperbarui.');
    }
}

