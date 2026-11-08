<?php

use App\Http\Controllers\BahanBakuController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\BiayaOperasionalController;
use App\Http\Controllers\DasborController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\LabaRugiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PrediksiController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\TutupBukuController;
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

    Route::get('/dasbor', [DasborController::class, 'index'])->name('dasbor');

    // Resource membuat 6 rute sekaligus: index, create, store, edit, update, destroy.
    // Rute show (lihat detail) tidak dipakai.
    Route::resource('bahan-baku', BahanBakuController::class)->except('show');
    Route::resource('menu', MenuController::class)->except('show');

    // Barang masuk hanya bisa dilihat dan ditambah, tidak bisa diubah atau dihapus.
    Route::resource('barang-masuk', BarangMasukController::class)->only(['index', 'create', 'store']);

    // Kasir: halaman mencatat transaksi baru (kasir dan owner).
    Route::get('/kasir', [KasirController::class, 'index']);
    Route::post('/kasir', [KasirController::class, 'store']);

    // Alamat lama formulir transaksi diarahkan ke halaman kasir.
    // Ditulis sebelum resource supaya "create" tidak dibaca sebagai nomor transaksi.
    Route::redirect('/transaksi/create', '/kasir');

    // Riwayat transaksi: hanya daftar dan detail. Tidak bisa diubah atau dihapus.
    Route::resource('transaksi', TransaksiController::class)->only(['index', 'show']);

    // Tutup buku harian, bisa diakses kasir dan owner.
    Route::get('/tutup-buku', [TutupBukuController::class, 'index']);
    Route::post('/tutup-buku', [TutupBukuController::class, 'store']);

    // Halaman khusus owner ditaruh di grup ini.
    Route::middleware('peran:owner')->group(function () {
        // Biaya operasional boleh diubah dan dihapus, jadi semua rute dipakai kecuali show.
        Route::resource('biaya-operasional', BiayaOperasionalController::class)->except('show');

        Route::get('/laba-rugi', [LabaRugiController::class, 'index']);

        // Prediksi: GET menampilkan hasil, POST (tombol Hitung Prediksi) menghitung dan menyimpan.
        Route::get('/prediksi', [PrediksiController::class, 'index']);
        Route::post('/prediksi', [PrediksiController::class, 'store']);

        // Laporan penjualan dan persediaan.
        Route::get('/laporan', [LaporanController::class, 'index']);
    });
});
