<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Model untuk tabel tutup_buku (rekap penjualan dan laba per hari).
// Tidak punya relasi karena tabel ini tidak merujuk dan tidak dirujuk tabel lain.
class TutupBuku extends Model
{
    protected $table = 'tutup_buku';

    protected $fillable = [
        'tanggal',
        'total_penjualan',
        'total_laba',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total_penjualan' => 'decimal:2',
        'total_laba' => 'decimal:2',
    ];
}
