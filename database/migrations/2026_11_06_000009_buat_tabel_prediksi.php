<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tabel hasil peramalan kebutuhan bahan baku dengan Moving Average.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prediksi', function (Blueprint $table) {
            $table->id();
            // Bahan baku tidak boleh dihapus jika sudah punya riwayat prediksi.
            $table->foreignId('bahan_baku_id')->constrained('bahan_baku')->restrictOnDelete();
            // Tanggal yang diramalkan.
            $table->date('tanggal');
            // Banyaknya periode yang dipakai untuk menghitung rata-rata, misalnya 3.
            $table->unsignedTinyInteger('periode_n');
            $table->enum('metode', ['SMA', 'WMA']);
            $table->decimal('nilai_prediksi', 10, 3);
            // Nilai aktual dan APE baru terisi setelah tanggal tersebut lewat.
            $table->decimal('nilai_aktual', 10, 3)->nullable();
            // APE = Absolute Percentage Error, dalam persen.
            $table->decimal('ape', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prediksi');
    }
};
