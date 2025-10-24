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
        Schema::create('next_recitations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Create the pivot table for next_recitation_surah
        Schema::create('next_recitation_surah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('next_recitation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('surah_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['ayah', 'page'])->default('ayah');
            $table->integer('fromAyeh')->nullable();
            $table->integer('toAyeh')->nullable();
            $table->integer('fromPage')->nullable();
            $table->integer('toPage')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('next_recitation_surah');
        Schema::dropIfExists('next_recitations');
    }
};
