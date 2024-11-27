<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kategori_artikels', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // Nama kategori
            $table->string('slug')->unique(); // URL-friendly slug
            $table->string('thumbnail')->nullable(); // Thumbnail kategori (opsional)
            $table->text('deskripsi')->nullable(); // Deskripsi kategori (opsional)
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori_artikels');
    }
};
