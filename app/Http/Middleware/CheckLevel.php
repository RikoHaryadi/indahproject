<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckLevel
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$levels)
    {
        // Ambil level user dari session (atau Auth::user()->level)
        $userLevel = (int) session('user_level');

        // Konversi parameter middleware menjadi integer
        $allowed = array_map('intval', $levels);

        // Jika level user tidak termasuk dalam daftar yang diizinkan, tolak
        if (! in_array($userLevel, $allowed, true)) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
