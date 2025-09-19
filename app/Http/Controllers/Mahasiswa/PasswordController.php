<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    public function edit()
    {
        return view('mahasiswa.password');
    }

    public function update(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();
        $user->password = Hash::make($request->password);
        $user->save();

        // Logout sesi lain (opsional namun disarankan)
        try {
            Auth::logoutOtherDevices($request->password);
        } catch (\Throwable $e) {
            // Lewatkan jika driver tidak mendukung
        }

        
        return back()->with('status', 'Password berhasil diperbarui.');
    }
}

