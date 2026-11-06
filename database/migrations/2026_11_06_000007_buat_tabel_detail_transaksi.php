<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tabel rincian isi setiap transaksi: menu apa, berapa porsi, dan labanya.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_transaksi', function (Blueprint $table) {
            $table->id();
            // Jika transaksi dihapus, rinciannya ikut terhapus.
            $table->foreignId('transaksi_id')->constrained('transaksi')->cascadeOnDelete();
            // Menu tidak boleh dihapus jika sudah pernah terjual.
            $table->foreignId('menu_id')->constrained('menu')->restrictOnDelete();
            // Jumlah porsi selalu bilangan bulat.
            $table->unsignedInteger('jumlah');
            // Harga jual dan HPP per porsi disalin saat transaksi terjadi,
            // supaya laporan lama tidak berubah jika harga diubah kemudian.
            $table->decimal('harga_jual', 12, 2);
            $table->decimal('hpp', 12, 2);
            // Laba kotor baris ini = (harga_jual - hpp) x jumlah.
            $table->decimal('laba_kotor', 14, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_transaksi');
    }
};
