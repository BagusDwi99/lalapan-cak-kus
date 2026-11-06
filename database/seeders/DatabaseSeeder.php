<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    // Urutan pemanggilan penting: tabel induk diisi lebih dulu.
    // Tabel prediksi dan tutup_buku sengaja tidak diisi; keduanya diisi oleh sistem.
    // barang_masuk diisi oleh DetailTransaksiSeeder (simulasi belanja harian bersama penjualan).
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            BahanBakuSeeder::class,
            MenuSeeder::class,
            MenuBahanBakuSeeder::class,
            TransaksiSeeder::class,
            DetailTransaksiSeeder::class,
            BiayaOperasionalSeeder::class,
        ]);
    }
}
