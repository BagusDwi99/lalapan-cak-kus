<?php

namespace Database\Seeders;

use App\Models\BiayaOperasional;
use Illuminate\Database\Seeder;

// Mengisi biaya operasional untuk bulan berjalan.
class BiayaOperasionalSeeder extends Seeder
{
    public function run(): void
    {
        // Periode diisi tanggal 1 bulan ini, contoh: 2026-11-01.
        $periode = now()->startOfMonth()->toDateString();

        $daftarBiaya = [
            ['nama' => 'Sewa Tempat', 'jumlah' => 2000000],
            ['nama' => 'Listrik',     'jumlah' => 450000],
            ['nama' => 'Gas LPG',     'jumlah' => 600000],
            ['nama' => 'Air',         'jumlah' => 150000],
            ['nama' => 'Gaji Kasir',  'jumlah' => 2500000],
        ];

        foreach ($daftarBiaya as $biaya) {
            BiayaOperasional::create($biaya + ['periode' => $periode]);
        }
    }
}
