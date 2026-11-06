<?php

namespace Database\Seeders;

use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Database\Seeder;

// Mengisi nota transaksi selama 30 hari terakhir, 15 sampai 25 nota per hari.
// Kolom total diisi 0 dulu, lalu diperbarui oleh DetailTransaksiSeeder
// setelah isi tiap nota diketahui.
class TransaksiSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(2);

        $kasir = User::where('peran', 'kasir')->firstOrFail();

        // Hari ke-0 = 30 hari lalu, hari ke-29 = kemarin.
        for ($hari = 0; $hari < 30; $hari++) {
            $tanggal = now()->subDays(30 - $hari)->toDateString();
            $jumlahNota = mt_rand(15, 25);

            for ($i = 0; $i < $jumlahNota; $i++) {
                Transaksi::create([
                    'tanggal' => $tanggal,
                    'total' => 0,
                    'user_id' => $kasir->id,
                ]);
            }
        }
    }
}
