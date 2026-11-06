<?php

namespace Database\Seeders;

use App\Models\BiayaOperasional;
use Illuminate\Database\Seeder;

// Mengisi biaya operasional untuk bulan lalu dan bulan berjalan.
//
// Periode dihitung dari tanggal seeder dijalankan, sama seperti data transaksi
// (30 hari terakhir). Jika dijalankan November 2026: bulan lalu = Oktober 2026,
// bulan berjalan = November 2026. Dengan begitu biaya selalu jatuh di bulan
// yang sama dengan data penjualannya, dan laporan laba rugi bisa didemokan.
class BiayaOperasionalSeeder extends Seeder
{
    public function run(): void
    {
        // Periode diisi tanggal 1 bulan tersebut, contoh: 2026-10-01.
        // subMonthNoOverflow: mundur satu bulan tanpa "meloncat" (31 Maret -> 28/29 Februari, bukan 3 Maret).
        $bulanLalu = now()->subMonthNoOverflow()->startOfMonth()->toDateString();
        $bulanIni = now()->startOfMonth()->toDateString();

        // Sewa dan gaji tetap; listrik, gas, dan air sedikit berbeda tiap bulan.
        $daftarBiaya = [
            $bulanLalu => [
                'Sewa Tempat' => 2000000,
                'Listrik'     => 430000,
                'Gas LPG'     => 580000,
                'Air'         => 140000,
                'Gaji Kasir'  => 2500000,
            ],
            $bulanIni => [
                'Sewa Tempat' => 2000000,
                'Listrik'     => 450000,
                'Gas LPG'     => 600000,
                'Air'         => 150000,
                'Gaji Kasir'  => 2500000,
            ],
        ];

        foreach ($daftarBiaya as $periode => $biayaBulanIni) {
            foreach ($biayaBulanIni as $nama => $jumlah) {
                BiayaOperasional::create([
                    'nama' => $nama,
                    'jumlah' => $jumlah,
                    'periode' => $periode,
                ]);
            }
        }
    }
}
