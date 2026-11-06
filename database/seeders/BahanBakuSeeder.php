<?php

namespace Database\Seeders;

use App\Models\BahanBaku;
use Illuminate\Database\Seeder;

// Mengisi 8 bahan baku.
// Stok dan harga rata-rata sengaja dimulai dari 0. Nilai akhirnya dihitung
// oleh DetailTransaksiSeeder dari riwayat barang masuk dikurangi pemakaian,
// supaya stok dan harga rata-rata konsisten dengan data lainnya.
class BahanBakuSeeder extends Seeder
{
    public function run(): void
    {
        $daftarBahan = [
            ['nama' => 'Ayam Potong',   'satuan' => 'potong', 'stok_minimum' => 40],
            ['nama' => 'Lele',          'satuan' => 'ekor',   'stok_minimum' => 20],
            ['nama' => 'Bebek',         'satuan' => 'potong', 'stok_minimum' => 10],
            ['nama' => 'Ikan Laut',     'satuan' => 'ekor',   'stok_minimum' => 8],
            ['nama' => 'Daging Sapi',   'satuan' => 'kg',     'stok_minimum' => 2],
            ['nama' => 'Beras',         'satuan' => 'kg',     'stok_minimum' => 25],
            ['nama' => 'Minyak Goreng', 'satuan' => 'liter',  'stok_minimum' => 5],
            ['nama' => 'Sayuran',       'satuan' => 'kg',     'stok_minimum' => 4],
        ];

        foreach ($daftarBahan as $bahan) {
            BahanBaku::create($bahan + ['stok' => 0, 'harga_rata_rata' => 0]);
        }
    }
}
