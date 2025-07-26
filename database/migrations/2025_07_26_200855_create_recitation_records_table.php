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
            $table->smallInteger('fromAyeh');
            $table->smallInteger('toAyeh');
            $table->float('score');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('surah_id');

            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('surah_id')->references('id')->on('surahs')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recitation_records');
    }
};
