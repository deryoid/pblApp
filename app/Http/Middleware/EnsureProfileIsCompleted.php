<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileIsCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Pengecualian rute agar pengguna tidak terkena redirect loop
            if ($request->routeIs('profile.*', 'logout', 'home', 'showcase.*')) {
                return $next($request);
            }

            if (! $user->isProfileComplete()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Profil belum lengkap.',
                        'message' => 'Anda wajib melengkapi data profil terlebih dahulu untuk dapat mengakses menu sistem.',
                        'redirect' => route('profile.edit'),
                    ], 403);
                }

                return redirect()->route('profile.edit')->with(
                    'warning',
                    'Akses Menu Dibatasi: Silakan perbarui dan lengkapi data profil Anda (No. HP/WhatsApp, Kelas, dan Biodata) sebelum mengakses menu aplikasi.'
                );
            }
        }

        return $next($request);
    }
}
