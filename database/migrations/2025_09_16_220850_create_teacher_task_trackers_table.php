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
        Schema::create('teacher_task_trackers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->date('date');
            
            // مهام المعلم
            $table->boolean('attendance_taken')->default(false)->comment('هل أخذ الحضور');
            $table->boolean('recitation_recorded')->default(false)->comment('هل سجل تسجيلات التلاوة');
            $table->boolean('next_recitation_set')->default(false)->comment('هل سجل التلاوة التالية');
            $table->boolean('whatsapp_sent')->default(false)->comment('هل أرسل على الواتساب');
            $table->boolean('homework_sent')->default(false)->comment('هل أرسل الواجبات');
            
            // ملاحظات إضافية
            $table->text('notes')->nullable()->comment('ملاحظات إضافية');
            
            $table->timestamps();
            
            // فهرس فريد لكل معلم ومادة وتاريخ
            $table->unique(['teacher_id', 'subject_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_task_trackers');
    }
};