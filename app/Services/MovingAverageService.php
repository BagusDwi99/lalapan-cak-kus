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
        $totalStok = $stokLama + $jumlahMasuk;

        // Jika stok masih kosong, harga rata-rata sama dengan harga beli.
        if ($totalStok <= 0) {
            return round($hargaBeli, 2);
        }

        $nilaiStokLama = $stokLama * $hargaRataRataLama;
        $nilaiBarangMasuk = $jumlahMasuk * $hargaBeli;

        return round(($nilaiStokLama + $nilaiBarangMasuk) / $totalStok, 2);
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
     */
    public function hitungHppPerPorsi(Menu $menu): float
    {
        $hpp = 0;

        foreach ($menu->bahanBaku as $bahan) {
            $hpp += $bahan->pivot->jumlah * $bahan->harga_rata_rata;
        }

        return round($hpp, 2);
    }
}
