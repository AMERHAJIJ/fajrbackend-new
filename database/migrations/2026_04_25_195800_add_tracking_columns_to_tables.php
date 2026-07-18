<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. تحديث جدول الحضور
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'teacher_id')) {
                $table->foreignId('teacher_id')->nullable()->constrained('users')->onDelete('cascade');
            }
        });

        // 2. تحديث جدول سجلات التلاوة
        Schema::table('recitation_records', function (Blueprint $table) {
            if (!Schema::hasColumn('recitation_records', 'teacher_id')) {
                $table->foreignId('teacher_id')->nullable()->constrained('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('recitation_records', 'subject_id')) {
                $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('cascade');
            }
        });

        // 3. تحديث جدول التلاوات القادمة
        Schema::table('next_recitations', function (Blueprint $table) {
            if (!Schema::hasColumn('next_recitations', 'teacher_id')) {
                $table->foreignId('teacher_id')->nullable()->constrained('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('next_recitations', 'subject_id')) {
                $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropColumn('teacher_id');
        });

        Schema::table('recitation_records', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropForeign(['subject_id']);
            $table->dropColumn(['teacher_id', 'subject_id']);
        });

        Schema::table('next_recitations', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropForeign(['subject_id']);
            $table->dropColumn(['teacher_id', 'subject_id']);
        });
    }
};
