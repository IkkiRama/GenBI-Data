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
        Schema::create('developers', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('role', 100);
            $table->text('deskripsi_role')->nullable();
            $table->string('periode', 100)->nullable();
            $table->string('sosmed_ig')->nullable();
            $table->string('sosmed_wa', 20)->nullable();
            $table->string('image')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            $table->softDeletes(); // deleted_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('developers');
    }
};
