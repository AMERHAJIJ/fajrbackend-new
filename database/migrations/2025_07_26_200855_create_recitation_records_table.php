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
        Schema::create('recitation_records', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->decimal('score', 5, 2);
            $table->unsignedBigInteger('student_id');
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Create the pivot table
        Schema::create('recitation_record_surah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recitation_record_id')->constrained()->cascadeOnDelete();
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
        Schema::dropIfExists('recitation_record_surah');
        Schema::dropIfExists('recitation_records');
    }
};
