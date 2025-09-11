<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Evaluator
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
        if (Auth::user()->role != 'evaluator') {
            // Gagal login
            Alert::toast('Kamu Tidak Memiliki Akses Evaluator', 'error');
            return redirect()->back();
        }

        return $next($request);
    }
}
