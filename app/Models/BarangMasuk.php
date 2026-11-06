<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Model untuk tabel barang_masuk (pembelian bahan baku).
class BarangMasuk extends Model
{
    protected $table = 'barang_masuk';

    protected $fillable = [
        'bahan_baku_id',
        'tanggal',
        'jumlah',
        'harga_beli',
        'harga_rata_rata_setelah',
    ];

    // 'date' membuat tanggal terbaca sebagai objek tanggal (Carbon),
    // jadi bisa diformat, misalnya $barangMasuk->tanggal->format('d-m-Y').
    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'decimal:3',
        'harga_beli' => 'decimal:2',
        // Harga rata-rata bahan baku tepat setelah barang masuk ini dicatat.
        'harga_rata_rata_setelah' => 'decimal:2',
    ];

    // Catatan barang masuk ini milik satu bahan baku.
    public function bahanBaku(): BelongsTo
    {
        return $this->belongsTo(BahanBaku::class);
    }
}
