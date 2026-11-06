<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

// Mengisi 7 menu beserta harga jualnya.
class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $daftarMenu = [
            ['nama' => 'Ayam Goreng',      'harga_jual' => 20000],
            ['nama' => 'Lele Goreng',      'harga_jual' => 17000],
            ['nama' => 'Bebek Goreng',     'harga_jual' => 35000],
            ['nama' => 'Ikan Laut Goreng', 'harga_jual' => 30000],
            ['nama' => 'Soto Ayam',        'harga_jual' => 18000],
            ['nama' => 'Bakso Sapi',       'harga_jual' => 20000],
            ['nama' => 'Nasi Goreng',      'harga_jual' => 15000],
        ];

        foreach ($daftarMenu as $menu) {
            Menu::create($menu);
        }
    }
}
