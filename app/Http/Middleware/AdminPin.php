<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminPin
{
    /**
     * Handle admin PIN check via session.
     */
    public function handle(Request $request, Closure $next)
    {
        if (! $request->session()->has('admin_authenticated')) {
            return redirect()->route('konfigurasi.pin');
        }

        return $next($request);
    }
}
