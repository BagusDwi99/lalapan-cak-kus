<?php

namespace App\Models;

use App\Services\MovingAverageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Model untuk tabel bahan_baku.
class BahanBaku extends Model
{
    // Nama tabel ditulis manual karena Laravel akan menebak "bahan_bakus" (bahasa Inggris).
    protected $table = 'bahan_baku';

    // Kolom yang boleh diisi lewat create() atau update().
    protected $fillable = [
        'nama',
        'satuan',
        'stok',
        'stok_minimum',
        'harga_rata_rata',
    ];

    // Mengubah tipe data saat dibaca dari basis data.
    // decimal:3 artinya angka dengan 3 digit di belakang koma.
    protected $casts = [
        'stok' => 'decimal:3',
        'stok_minimum' => 'decimal:3',
        'harga_rata_rata' => 'decimal:2',
    ];

    // Menghitung harga rata-rata baru jika bahan ini menerima barang masuk.
    // Rumusnya sendiri berada di MovingAverageService (aturan proyek: semua rumus
    // Moving Average di satu tempat). Method ini memberikan stok dan harga rata-rata
    // milik bahan ini ke service tersebut.
    public function hitungHargaRataRataBaru(float $jumlahMasuk, float $hargaBeli): float
    {
        return app(MovingAverageService::class)->hitungHargaRataRata(
            $this->stok,
            $this->harga_rata_rata,
            $jumlahMasuk,
            $hargaBeli
        );
    }

    // Satu bahan baku dipakai di banyak menu (lewat tabel resep menu_bahan_baku).
    public function menu(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'menu_bahan_baku')
            ->using(MenuBahanBaku::class)
            ->withPivot('jumlah')
            ->withTimestamps();
    }

    // Satu bahan baku punya banyak catatan barang masuk.
    public function barangMasuk(): HasMany
    {
        return $this->hasMany(BarangMasuk::class);
    }

    // Satu bahan baku punya banyak hasil prediksi.
    public function prediksi(): HasMany
    {
        return $this->hasMany(Prediksi::class);
    }
}
