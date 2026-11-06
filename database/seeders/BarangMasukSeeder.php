<?php

namespace Database\Seeders;

use App\Models\BahanBaku;
use App\Models\BarangMasuk;
use Illuminate\Database\Seeder;

// Mengisi riwayat pembelian bahan baku selama 30 hari terakhir.
// Seeder ini hanya mencatat pembelian. Perubahan stok dan harga rata-rata
// dihitung urut per tanggal di DetailTransaksiSeeder.
class BarangMasukSeeder extends Seeder
{
    public function run(): void
    {
        // Angka acak dibuat tetap (seed 1) supaya data selalu sama setiap kali seeder dijalankan.
        mt_srand(1);

        // interval = beli setiap berapa hari, jumlah = banyaknya sekali beli,
        // harga = harga beli dasar per satuan (nanti dinaik-turunkan sedikit).
        $polaPembelian = [
            'Ayam Potong'   => ['interval' => 3, 'jumlah' => 120, 'harga' => 9000],
            'Lele'          => ['interval' => 3, 'jumlah' => 50,  'harga' => 3500],
            'Bebek'         => ['interval' => 5, 'jumlah' => 40,  'harga' => 22000],
            'Ikan Laut'     => ['interval' => 5, 'jumlah' => 35,  'harga' => 18000],
            'Daging Sapi'   => ['interval' => 7, 'jumlah' => 9,   'harga' => 130000],
            'Beras'         => ['interval' => 7, 'jumlah' => 100, 'harga' => 13000],
            'Minyak Goreng' => ['interval' => 7, 'jumlah' => 25,  'harga' => 17000],
            'Sayuran'       => ['interval' => 2, 'jumlah' => 10,  'harga' => 12000],
        ];

        foreach ($polaPembelian as $namaBahan => $pola) {
            $bahan = BahanBaku::where('nama', $namaBahan)->firstOrFail();

            // Hari ke-0 = 30 hari lalu, hari ke-29 = kemarin.
            for ($hari = 0; $hari < 30; $hari += $pola['interval']) {
                // Harga beli naik-turun antara -5% sampai +5%, dibulatkan ke ratusan.
                $persen = mt_rand(-5, 5) / 100;
                $hargaBeli = round($pola['harga'] * (1 + $persen), -2);

                BarangMasuk::create([
                    'bahan_baku_id' => $bahan->id,
                    'tanggal' => now()->subDays(30 - $hari)->toDateString(),
                    'jumlah' => $pola['jumlah'],
                    'harga_beli' => $hargaBeli,
                ]);
            }
        }
    }
}
