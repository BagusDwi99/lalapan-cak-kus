<?php

use App\Http\Controllers\BahanBakuController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\BiayaOperasionalController;
use App\Http\Controllers\LabaRugiController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\TransaksiController;
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

    // Resource membuat 6 rute sekaligus: index, create, store, edit, update, destroy.
    // Rute show (lihat detail) tidak dipakai.
    Route::resource('bahan-baku', BahanBakuController::class)->except('show');
    Route::resource('menu', MenuController::class)->except('show');

    // Barang masuk hanya bisa dilihat dan ditambah, tidak bisa diubah atau dihapus.
    Route::resource('barang-masuk', BarangMasukController::class)->only(['index', 'create', 'store']);

    // Transaksi hanya bisa dilihat, dilihat detailnya, dan ditambah. Tidak bisa diubah atau dihapus.
    Route::resource('transaksi', TransaksiController::class)->only(['index', 'create', 'store', 'show']);

    // Halaman khusus owner nanti ditaruh di grup ini.
    Route::middleware('peran:owner')->group(function () {
        // Biaya operasional boleh diubah dan dihapus, jadi semua rute dipakai kecuali show.
        Route::resource('biaya-operasional', BiayaOperasionalController::class)->except('show');

        Route::get('/laba-rugi', [LabaRugiController::class, 'index']);
    });
});
