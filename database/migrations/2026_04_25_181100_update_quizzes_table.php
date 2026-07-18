<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            // جعل الفيديو اختيارياً
            $table->foreignId('video_id')->nullable()->change();
            
            // إضافة الربط بالمادة والأستاذ
            $table->foreignId('subject_id')->after('active')->nullable()->constrained('subjects')->onDelete('cascade');
            $table->foreignId('teacher_id')->after('subject_id')->nullable()->constrained('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->foreignId('video_id')->nullable(false)->change();
            $table->dropForeign(['subject_id']);
            $table->dropColumn('subject_id');
            $table->dropForeign(['teacher_id']);
            $table->dropColumn('teacher_id');
        });
    }
};
