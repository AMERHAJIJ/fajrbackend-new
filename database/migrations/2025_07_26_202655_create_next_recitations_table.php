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
            $table->smallInteger('fromAyeh');
            $table->smallInteger('toAyeh');
            $table->unsignedBigInteger('surah_id');
            $table->unsignedBigInteger('student_id');

            $table->foreign('surah_id')->references('id')->on('sur ahs')->onDelete('cascade');
             $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('next_recitations');
    }
};
