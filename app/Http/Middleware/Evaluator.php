<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;
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
        if (! Auth::check()) {
            Alert::toast('Anda Belum Login.', 'error');

            return redirect()->route('login');
        }

        if (! in_array(Auth::user()->role, ['evaluator', 'dosen', 'admin'], true)) {
            Alert::toast('Kamu Tidak Memiliki Akses Evaluator', 'error');

            return redirect()->back();
        }

        return $next($request);
    }
}
