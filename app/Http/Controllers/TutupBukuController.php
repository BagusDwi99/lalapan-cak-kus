<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\DetailTransaksi;
use App\Models\Transaksi;
use App\Models\TutupBuku;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;

// Mengatur tutup buku harian: merekap total penjualan dan laba kotor satu hari.
// Bisa dilakukan kasir maupun owner, setiap akhir hari operasional.
class TutupBukuController extends Controller
{
    // Halaman daftar tutup buku dan tombol "Tutup Buku Hari Ini".
    public function index(Request $request)
    {
        $daftarTutupBuku = TutupBuku::orderByDesc('tanggal')->paginate(15);

        // Data tutup buku hari ini, jika sudah ada (untuk menonaktifkan tombol).
        $tutupBukuHariIni = TutupBuku::whereDate('tanggal', today())->first();

        // Modal stok menipis hanya ditampilkan tepat setelah tutup buku berhasil.
        // Penanda ini dikirim dari store() lewat session dan hanya berlaku sekali.
        $tampilkanModalStok = session('tampilkan_modal_stok', false);
        $bahanMenipis = $tampilkanModalStok
            ? BahanBaku::stokMenipis()->orderBy('nama')->get()
            : collect();

        return view('tutup_buku.index', [
            'daftarTutupBuku' => $daftarTutupBuku,
            'tutupBukuHariIni' => $tutupBukuHariIni,
            'tampilkanModalStok' => $tampilkanModalStok,
            'bahanMenipis' => $bahanMenipis,
            // Total laba kotor hanya untuk owner, sama seperti modul transaksi dan menu.
            'tampilkanLaba' => $request->user()->bolehLihatLaba(),
        ]);
    }

    // Proses tutup buku untuk tanggal hari ini.
    public function store()
    {
        $hariIni = today();

        // 1. Satu tanggal hanya boleh ditutup sekali.
        if (TutupBuku::whereDate('tanggal', $hariIni)->exists()) {
            return back()->with('gagal', 'Tutup buku hari ini sudah dilakukan. Satu tanggal hanya bisa ditutup sekali.');
        }

        // 2. Tolak jika belum ada transaksi sama sekali hari ini.
        $transaksiHariIni = Transaksi::whereDate('tanggal', $hariIni);
        if (! $transaksiHariIni->exists()) {
            return back()->with('gagal',
                'Tutup buku ditolak: belum ada transaksi penjualan pada hari ini ('
                . $hariIni->format('d/m/Y') . '). Catat transaksi terlebih dahulu.');
        }

        // 3. Hitung total penjualan dan total laba kotor seluruh transaksi hari ini.
        $totalPenjualan = $transaksiHariIni->sum('total');
        $totalLaba = DetailTransaksi::whereHas('transaksi', fn ($query) => $query->whereDate('tanggal', $hariIni))
            ->sum('laba_kotor');

        // 4. Simpan ke tabel tutup_buku.
        try {
            TutupBuku::create([
                'tanggal' => $hariIni->toDateString(),
                'total_penjualan' => $totalPenjualan,
                'total_laba' => $totalLaba,
            ]);
        } catch (UniqueConstraintViolationException $e) {
            // Terjadi jika tombol ditekan dua kali sangat cepat atau oleh dua orang bersamaan:
            // kolom tanggal di basis data bersifat unique, jadi yang kedua ditolak.
            return back()->with('gagal', 'Tutup buku hari ini sudah dilakukan. Satu tanggal hanya bisa ditutup sekali.');
        }

        // 5. Kembali ke daftar, dengan pesan berhasil dan penanda untuk menampilkan modal stok.
        return redirect('/tutup-buku')
            ->with('berhasil', 'Tutup buku tanggal ' . $hariIni->format('d/m/Y') . ' berhasil.')
            ->with('tampilkan_modal_stok', true);
    }
}
