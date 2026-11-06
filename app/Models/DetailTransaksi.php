<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Model untuk tabel detail_transaksi (rincian menu dalam satu nota).
class DetailTransaksi extends Model
{
    protected $table = 'detail_transaksi';

    protected $fillable = [
        'transaksi_id',
        'menu_id',
        'jumlah',
        'harga_jual',
        'hpp',
        'laba_kotor',
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'harga_jual' => 'decimal:2',
        'hpp' => 'decimal:2',
        'laba_kotor' => 'decimal:2',
    ];

    // Baris detail ini milik satu transaksi.
    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class);
    }

    // Baris detail ini berisi satu menu.
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}
