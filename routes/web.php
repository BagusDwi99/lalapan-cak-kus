<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

// Halaman login hanya untuk tamu (pengguna yang belum masuk).
// Pengguna yang sudah masuk akan diarahkan ke /dasbor.
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Semua halaman di dalam grup ini wajib login (middleware auth).
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Alamat utama langsung diarahkan ke dasbor.
    Route::redirect('/', '/dasbor');

    Route::view('/dasbor', 'dasbor')->name('dasbor');

    // Halaman khusus owner nanti ditaruh di grup ini.
    Route::middleware('peran:owner')->group(function () {
        //
    });
});
