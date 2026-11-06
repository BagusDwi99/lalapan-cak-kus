<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Model untuk tabel menu.
class Menu extends Model
{
    protected $table = 'menu';

    protected $fillable = [
        'nama',
        'harga_jual',
    ];

    protected $casts = [
        'harga_jual' => 'decimal:2',
    ];

    // Satu menu memakai banyak bahan baku (resep).
    // withPivot('jumlah') supaya jumlah pemakaian per porsi ikut terbaca,
    // contoh: $menu->bahanBaku->first()->pivot->jumlah
    public function bahanBaku(): BelongsToMany
    {
        return $this->belongsToMany(BahanBaku::class, 'menu_bahan_baku')
            ->using(MenuBahanBaku::class)
            ->withPivot('jumlah')
            ->withTimestamps();
    }

    // Satu menu muncul di banyak baris detail transaksi.
    public function detailTransaksi(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class);
    }
}
