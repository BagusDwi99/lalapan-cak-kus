<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Middleware untuk membatasi halaman berdasarkan peran pengguna.
//
// Cara pakai di routes/web.php:
//   Route::get('/laporan', ...)->middleware('peran:owner');
// Artinya halaman itu hanya boleh dibuka oleh pengguna berperan owner.
class CekPeran
{
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        // Jika peran pengguna tidak ada dalam daftar yang diizinkan,
        // tampilkan halaman 403 (akses ditolak).
        if (! in_array($request->user()?->peran, $peran)) {
            abort(403, 'Halaman ini hanya untuk owner.');
        }

        // Jika diizinkan, lanjutkan ke halaman yang diminta.
        return $next($request);
    }
}
