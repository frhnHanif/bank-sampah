<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NasabahAccess
{
    /**
     * Izinkan akses jika:
     * 1. User sudah login sebagai pengurus (Auth::check), atau
     * 2. Session nasabah_id cocok dengan route parameter {id}.
     */
    public function handle(Request $request, Closure $next)
    {
        $routeId = $request->route('id');

        // Pengurus yang sudah login — selalu diizinkan
        if (Auth::check()) {
            return $next($request);
        }

        // Nasabah yang sudah login via cek rekening — hanya untuk dirinya sendiri
        if (session('nasabah_id') && (int) session('nasabah_id') === (int) $routeId) {
            return $next($request);
        }

        // Selain itu, redirect ke halaman cek rekening
        return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
    }
}
