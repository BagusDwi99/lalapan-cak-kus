<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tabel penghubung (pivot) antara menu dan bahan baku.
// Isinya resep: satu porsi menu memakai bahan baku apa saja dan berapa banyak.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_bahan_baku', function (Blueprint $table) {
            $table->id();
            // Jika menu dihapus, resepnya ikut terhapus.
            $table->foreignId('menu_id')->constrained('menu')->cascadeOnDelete();
            // Bahan baku tidak boleh dihapus selama masih dipakai di resep.
            $table->foreignId('bahan_baku_id')->constrained('bahan_baku')->restrictOnDelete();
            // Jumlah pemakaian per porsi, misalnya 0,150 kg beras.
            $table->decimal('jumlah', 10, 3);
            $table->timestamps();

            // Satu bahan baku hanya boleh muncul sekali dalam resep satu menu.
            $table->unique(['menu_id', 'bahan_baku_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_bahan_baku');
    }
};
