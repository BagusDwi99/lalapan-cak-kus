<?php

namespace Database\Seeders;

use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Random\Engine\Mt19937;
use Random\Randomizer;

// Mengisi nota transaksi selama 30 hari terakhir, 15 sampai 25 nota per hari.
// Kolom total diisi 0 dulu, lalu diperbarui oleh DetailTransaksiSeeder
// setelah isi tiap nota diketahui.
class TransaksiSeeder extends Seeder
{
    public function run(): void
    {
        // Angka acak jumlah nota dibuat tetap (seed 2) supaya data selalu sama.
        mt_srand(2);

        // Angka acak waktu pencatatan memakai pembangkit TERPISAH (seed 4), supaya
        // urutan angka acak jumlah nota di atas tidak berubah.
        $acakWaktu = new Randomizer(new Mt19937(4));

        $kasir = User::where('peran', 'kasir')->firstOrFail();

        // Hari ke-0 = 30 hari lalu, hari ke-29 = kemarin.
        for ($hari = 0; $hari < 30; $hari++) {
            $tanggal = now()->subDays(30 - $hari)->toDateString();
            $jumlahNota = mt_rand(15, 25);

            // Waktu pencatatan tiap nota: acak selama jam buka warung, 10.00 sampai 21.00.
            // Disimpan sebagai detik sejak tengah malam, lalu diurutkan supaya nota yang
            // dicatat lebih dulu juga bernomor lebih kecil.
            $daftarDetik = [];
            for ($i = 0; $i < $jumlahNota; $i++) {
                $daftarDetik[] = $acakWaktu->getInt(10 * 3600, 21 * 3600);
            }
            sort($daftarDetik);

            foreach ($daftarDetik as $detik) {
                $waktuDicatat = Carbon::parse($tanggal)->addSeconds($detik);

                // forceCreate: created_at bukan kolom fillable, jadi diisi lewat forceCreate.
                // Karena nilainya sudah ditentukan, Laravel tidak menimpanya dengan waktu sekarang.
                Transaksi::forceCreate([
                    'tanggal' => $tanggal,
                    'total' => 0,
                    'user_id' => $kasir->id,
                    'created_at' => $waktuDicatat,
                    'updated_at' => $waktuDicatat,
                ]);
            }
        }
    }
}
