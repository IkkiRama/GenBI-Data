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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();

            $table->uuid('uuid')->unique();
            $table->string('title')->unique();
            $table->text('description')->nullable();

            // Jadwal aktif kuis
            $table->timestamp('start_at')->nullable();   // kapan kuis mulai bisa dimainkan
            $table->timestamp('end_at')->nullable();     // kapan kuis berakhir

            // Durasi pengerjaan (dalam menit)
            $table->integer('duration_minutes')->default(10);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
