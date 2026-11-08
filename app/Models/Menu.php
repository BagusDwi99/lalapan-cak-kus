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

    /**
     * Apakah stok semua bahan baku menu ini cukup untuk membuat SATU porsi.
     * Dipakai halaman kasir untuk meredupkan menu yang tidak bisa dijual.
     * Pemeriksaan akhir tetap dilakukan TransaksiService saat transaksi disimpan.
     *
     * $menu harus punya relasi bahanBaku (beserta pivot jumlah).
     */
    public function cukupUntukSatuPorsi(): bool
    {
        foreach ($this->bahanBaku as $bahan) {
            // Stok bahan kurang dari pemakaian satu porsi: menu tidak bisa dibuat.
            if ((float) $bahan->stok < (float) $bahan->pivot->jumlah) {
                return false;
            }
        }

        return true;
    }

    /**
     * Menghitung HPP satu porsi menu ini dari harga rata-rata bahan bakunya.
     *
     * Rumus:
     *   HPP per porsi = jumlah (jumlah pemakaian tiap bahan x harga rata-rata bahan itu)
     *
     * Contoh Ayam Goreng: 1 potong ayam x 8.639,61 + 0,15 kg beras x 13.159,27 + ...
     *
     * $hargaRataRata (tidak wajib): daftar harga terbaru [bahan_baku_id => harga].
     * Dipakai saat transaksi penjualan, yaitu harga yang dibaca sambil dikunci (lockForUpdate),
     * supaya pasti harga paling baru. Jika tidak diisi, dipakai harga dari relasi bahanBaku.
     */
    public function hitungHppPerPorsi(?array $hargaRataRata = null): float
    {
        $hpp = 0;

        foreach ($this->bahanBaku as $bahan) {
            // Pakai harga terbaru jika diberikan, jika tidak pakai harga dari relasi.
            $harga = $hargaRataRata[$bahan->id] ?? $bahan->harga_rata_rata;

            // Jumlah pemakaian per porsi x harga rata-rata per satuan.
            $hpp += $bahan->pivot->jumlah * $harga;
        }

        return round($hpp, 2);
    }
}
