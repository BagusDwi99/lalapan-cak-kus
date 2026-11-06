<?php

namespace App\Services;

use App\Models\Menu;

// Semua rumus Moving Average dan MAPE dikumpulkan di kelas ini.
class MovingAverageService
{
    /**
     * Moving average cost method: menghitung harga rata-rata baru
     * saat ada barang masuk.
     *
     * Rumus:
     *   harga rata-rata baru = (stok lama x harga rata-rata lama + jumlah masuk x harga beli)
     *                          / (stok lama + jumlah masuk)
     *
     * Contoh: stok 10 kg harga rata-rata 12.000, lalu masuk 10 kg harga 14.000.
     *   (10 x 12.000 + 10 x 14.000) / 20 = 13.000
     */
    public function hitungHargaRataRata(
        float $stokLama,
        float $hargaRataRataLama,
        float $jumlahMasuk,
        float $hargaBeli
    ): float {
        // Jika stok lama kosong, harga rata-rata baru sama dengan harga beli.
        if ($stokLama <= 0) {
            return round($hargaBeli, 2);
        }

        $nilaiStokLama = $stokLama * $hargaRataRataLama;
        $nilaiBarangMasuk = $jumlahMasuk * $hargaBeli;

        return round(($nilaiStokLama + $nilaiBarangMasuk) / ($stokLama + $jumlahMasuk), 2);
    }

    /**
     * Menghitung HPP satu porsi menu dari harga rata-rata bahan baku saat ini.
     *
     * Rumus:
     *   HPP per porsi = jumlah (jumlah pemakaian tiap bahan x harga rata-rata bahan itu)
     *
     * Contoh Ayam Goreng: 1 potong ayam x 8.639,61 + 0,15 kg beras x 13.159,27 + ...
     *
     * $menu harus punya relasi bahanBaku (beserta pivot jumlah).
     *
     * $hargaRataRata (tidak wajib): daftar harga terbaru [bahan_baku_id => harga].
     * Dipakai saat transaksi penjualan, yaitu harga yang dibaca sambil dikunci (lockForUpdate),
     * supaya pasti harga paling baru. Jika tidak diisi, dipakai harga dari relasi menu.
     */
    public function hitungHppPerPorsi(Menu $menu, ?array $hargaRataRata = null): float
    {
        $hpp = 0;

        foreach ($menu->bahanBaku as $bahan) {
            $harga = $hargaRataRata[$bahan->id] ?? $bahan->harga_rata_rata;
            $hpp += $bahan->pivot->jumlah * $harga;
        }

        return round($hpp, 2);
    }
}
