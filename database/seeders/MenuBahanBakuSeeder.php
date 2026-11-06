<?php

namespace Database\Seeders;

use App\Models\BahanBaku;
use App\Models\Menu;
use Illuminate\Database\Seeder;

// Mengisi resep: bahan baku yang dipakai untuk satu porsi tiap menu.
class MenuBahanBakuSeeder extends Seeder
{
    public function run(): void
    {
        // Format: 'Nama Menu' => ['Nama Bahan Baku' => jumlah per porsi]
        $resep = [
            'Ayam Goreng' => [
                'Ayam Potong' => 1, 'Beras' => 0.15, 'Minyak Goreng' => 0.05, 'Sayuran' => 0.05,
            ],
            'Lele Goreng' => [
                'Lele' => 1, 'Beras' => 0.15, 'Minyak Goreng' => 0.05, 'Sayuran' => 0.05,
            ],
            'Bebek Goreng' => [
                'Bebek' => 1, 'Beras' => 0.15, 'Minyak Goreng' => 0.06, 'Sayuran' => 0.05,
            ],
            'Ikan Laut Goreng' => [
                'Ikan Laut' => 1, 'Beras' => 0.15, 'Minyak Goreng' => 0.06, 'Sayuran' => 0.05,
            ],
            'Soto Ayam' => [
                'Ayam Potong' => 0.5, 'Beras' => 0.15, 'Sayuran' => 0.05,
            ],
            'Bakso Sapi' => [
                'Daging Sapi' => 0.1, 'Sayuran' => 0.03,
            ],
            'Nasi Goreng' => [
                'Beras' => 0.2, 'Minyak Goreng' => 0.03, 'Sayuran' => 0.03,
            ],
        ];

        // Ambil id bahan baku berdasarkan nama, contoh: ['Beras' => 6, ...]
        $idBahan = BahanBaku::pluck('id', 'nama');

        foreach ($resep as $namaMenu => $daftarBahan) {
            $menu = Menu::where('nama', $namaMenu)->firstOrFail();

            // attach() mengisi tabel pivot menu_bahan_baku beserta kolom jumlah.
            foreach ($daftarBahan as $namaBahan => $jumlah) {
                $menu->bahanBaku()->attach($idBahan[$namaBahan], ['jumlah' => $jumlah]);
            }
        }
    }
}
