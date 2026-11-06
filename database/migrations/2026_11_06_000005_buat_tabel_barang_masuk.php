<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tabel pencatatan pembelian bahan baku (stok bertambah).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang_masuk', function (Blueprint $table) {
            $table->id();
            // Bahan baku tidak boleh dihapus jika sudah punya riwayat barang masuk.
            $table->foreignId('bahan_baku_id')->constrained('bahan_baku')->restrictOnDelete();
            $table->date('tanggal');
            $table->decimal('jumlah', 10, 3);
            // Harga beli per satuan pada pembelian ini.
            $table->decimal('harga_beli', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang_masuk');
    }
};
