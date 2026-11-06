<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

// Mengisi dua akun: owner dan kasir.
class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Kata sandi ditulis biasa. Model User otomatis meng-hash-nya
        // karena ada cast 'password' => 'hashed'.
        User::create([
            'name' => 'Cak Kus',
            'email' => 'owner@cakkus.test',
            'peran' => 'owner',
            'password' => 'password',
        ]);

        User::create([
            'name' => 'Kasir Warung',
            'email' => 'kasir@cakkus.test',
            'peran' => 'kasir',
            'password' => 'password',
        ]);
    }
}
