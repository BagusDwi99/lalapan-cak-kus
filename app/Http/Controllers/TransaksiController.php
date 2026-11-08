<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Illuminate\Http\Request;

// Mengatur halaman riwayat transaksi penjualan: daftar dan detail.
// Mencatat transaksi baru ada di KasirController (/kasir).
// Transaksi tidak bisa diubah atau dihapus.
class TransaksiController extends Controller
{
    // Halaman daftar transaksi dengan penyaring rentang tanggal.
    public function index(Request $request)
    {
        $filter = [
            'dari' => $request->query('dari'),
            'sampai' => $request->query('sampai'),
        ];

        // HPP dan laba kotor hanya untuk owner.
        $tampilkanLaba = $request->user()->bolehLihatLaba();

        $daftarTransaksi = Transaksi::query()
            ->with('user')
            // withSum: jumlahkan kolom di detail_transaksi untuk setiap transaksi.
            // Hasilnya tersedia sebagai $transaksi->jumlah_porsi dan $transaksi->total_laba.
            ->withSum('detailTransaksi as jumlah_porsi', 'jumlah')
            // Total laba hanya diambil dari basis data jika pengguna boleh melihatnya.
            ->when($tampilkanLaba, fn ($query) => $query->withSum('detailTransaksi as total_laba', 'laba_kotor'))
            ->when($filter['dari'], fn ($query, $tanggal) => $query->whereDate('tanggal', '>=', $tanggal))
            ->when($filter['sampai'], fn ($query, $tanggal) => $query->whereDate('tanggal', '<=', $tanggal))
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('transaksi.index', compact('daftarTransaksi', 'filter', 'tampilkanLaba'));
    }

    // Halaman detail satu transaksi.
    public function show(Request $request, Transaksi $transaksi)
    {
        $transaksi->load(['user', 'detailTransaksi.menu']);
        // HPP dan laba kotor hanya untuk owner. Kasir hanya melihat harga jual dan total.
        // Pemeriksaan ini di server: untuk kasir, kolom HPP dan laba tidak dikirim sama sekali ke browser.
        $tampilkanLaba = $request->user()->bolehLihatLaba();

        return view('transaksi.show', compact('transaksi', 'tampilkanLaba'));
    }
}
