<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // جعل الفئة اختيارية في المقالات
        Schema::table('blogs', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->change();
        });

        // جعل الربط اختيارياً في الملفات (للملفات العامة)
        Schema::table('files', function (Blueprint $table) {
            $table->string('object_type')->nullable()->change();
            $table->unsignedBigInteger('object_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable(false)->change();
        });

        Schema::table('files', function (Blueprint $table) {
            $table->string('object_type')->nullable(false)->change();
            $table->unsignedBigInteger('object_id')->nullable(false)->change();
        });
    }
};
