<?php

namespace App\Services;

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
}
