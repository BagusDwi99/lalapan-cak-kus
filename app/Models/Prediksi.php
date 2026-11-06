<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Model untuk tabel prediksi (hasil peramalan SMA dan WMA).
// Model ini hanya menyimpan dan membaca data.
// Rumus Moving Average dan MAPE tetap berada di app/Services/MovingAverageService.php.
class Prediksi extends Model
{
    protected $table = 'prediksi';

    protected $fillable = [
        'bahan_baku_id',
        'tanggal',
        'periode_n',
        'metode',
        'nilai_prediksi',
        'nilai_aktual',
        'ape',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'periode_n' => 'integer',
        'nilai_prediksi' => 'decimal:3',
        'nilai_aktual' => 'decimal:3',
        'ape' => 'decimal:2',
    ];

    // Prediksi ini untuk satu bahan baku.
    public function bahanBaku(): BelongsTo
    {
        return $this->belongsTo(BahanBaku::class);
    }
}
