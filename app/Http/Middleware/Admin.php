<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;
use Symfony\Component\HttpFoundation\Response;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            // Gagal login
            Alert::toast('Anda Belum Login.', 'error');
            return redirect()->route('login');
        }
        if (Auth::user()->role != 'admin') {
            // Gagal login
            Alert::toast('Kamu Tidak Memiliki Akses Admin', 'error');
            return redirect()->back();
        }

        return $next($request);
    }
}
