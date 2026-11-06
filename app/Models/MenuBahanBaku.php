<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

// Model untuk tabel pivot menu_bahan_baku (resep menu).
// Turunan dari Pivot, bukan Model biasa, karena dipakai oleh relasi belongsToMany
// di Menu dan BahanBaku lewat ->using(MenuBahanBaku::class).
class MenuBahanBaku extends Pivot
{
    protected $table = 'menu_bahan_baku';

    // Tabel ini punya kolom id sendiri yang bertambah otomatis.
    public $incrementing = true;

    protected $fillable = [
        'menu_id',
        'bahan_baku_id',
        'jumlah',
    ];

    protected $casts = [
        'jumlah' => 'decimal:3',
    ];

    // Baris resep ini milik satu menu.
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    // Baris resep ini memakai satu bahan baku.
    public function bahanBaku(): BelongsTo
    {
        return $this->belongsTo(BahanBaku::class);
    }
}
