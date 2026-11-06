<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Model untuk tabel transaksi (satu nota penjualan).
class Transaksi extends Model
{
    protected $table = 'transaksi';

    protected $fillable = [
        'tanggal',
        'total',
        'user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total' => 'decimal:2',
    ];

    // Transaksi ini dicatat oleh satu user (kasir).
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Satu transaksi berisi banyak baris detail.
    public function detailTransaksi(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class);
    }
}
