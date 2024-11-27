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
        Schema::create('strukturs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap', 255);
            $table->enum('type', ['president', 'secretary', 'treasure', 'deputy']);
            $table->string('jabatan', 255);
            $table->string('periode', 255)->nullable();
            $table->string('universitas', 255);
            $table->text('quote')->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('strukturs');
    }
};
