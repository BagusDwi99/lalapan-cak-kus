<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransaksiRequest;
use App\Models\Menu;
use App\Models\TutupBuku;
use App\Services\TransaksiService;

// Halaman kasir: mencatat transaksi penjualan baru dengan cepat.
// Bisa diakses kasir maupun owner. Riwayat transaksi ada di TransaksiController.
class KasirController extends Controller
{
    // Menampilkan halaman kasir: daftar menu di kiri, keranjang pesanan di kanan.
    public function index()
    {
        // Data menu untuk halaman kasir. Sengaja hanya id, nama, harga jual, dan status stok:
        // HPP, harga rata-rata, dan stok bahan tidak dikirim ke browser.
        $daftarMenu = Menu::with('bahanBaku')->orderBy('nama')->get()->map(fn ($menu) => [
            'id' => $menu->id,
            'nama' => $menu->nama,
            'harga' => (float) $menu->harga_jual,
            'cukup' => $menu->cukupUntukSatuPorsi(),
        ]);

        return view('kasir.index', [
            'daftarMenu' => $daftarMenu,
            // Jika hari ini sudah ditutup buku, tombol Simpan dinonaktifkan sejak awal.
            'sudahTutupBuku' => TutupBuku::whereDate('tanggal', today())->exists(),
            // Jika penyimpanan gagal (misalnya stok tidak cukup), isi keranjang dikembalikan
            // dari data formulir sebelumnya, supaya kasir tidak perlu memilih ulang.
            'keranjangAwal' => array_values(old('item', [])),
        ]);
    }

    // Menyimpan transaksi. Validasi lewat TransaksiRequest, seluruh prosesnya di TransaksiService.
    public function store(TransaksiRequest $request, TransaksiService $transaksiService)
    {
        $transaksi = $transaksiService->simpan(
            // Tanggal selalu hari ini menurut server (WITA). Tanggal yang dikirim dari formulir diabaikan.
            today()->toDateString(),
            $request->validated('item'),
            $request->user()->id
        );

        // Tetap di halaman kasir supaya bisa langsung melayani pembeli berikutnya.
        // Ringkasan transaksi dikirim untuk pesan berhasil beserta tautan detailnya.
        return redirect('/kasir')->with('transaksiTersimpan', [
            'id' => $transaksi->id,
            'total' => (float) $transaksi->total,
        ]);
    }
}
