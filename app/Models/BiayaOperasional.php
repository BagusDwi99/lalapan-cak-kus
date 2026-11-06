<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Model untuk tabel biaya_operasional (biaya bulanan seperti listrik dan gas).
// Tidak punya relasi karena tabel ini tidak merujuk dan tidak dirujuk tabel lain.
class BiayaOperasional extends Model
{
    protected $table = 'biaya_operasional';

    protected $fillable = [
        'nama',
        'jumlah',
        'periode',
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'periode' => 'date',
    ];
}
