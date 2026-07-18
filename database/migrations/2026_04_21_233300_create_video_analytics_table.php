<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('video_id')->constrained('videos')->onDelete('cascade');
            
            // العوامل التي اتفقنا عليها
            $table->integer('watched_duration')->default(0); // بالثواني
            $table->integer('total_video_duration')->default(0); // بالثواني
            $table->integer('pause_count')->default(0);
            $table->integer('forward_skip_count')->default(0);
            $table->integer('backward_skip_count')->default(0);
            $table->float('playback_rate')->default(1.0);
            $table->integer('app_switch_count')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->integer('total_session_time')->default(0); // الوقت الإجمالي الذي قضاه في الصفحة
            
            // نتائج الذكاء الاصطناعي لاحقاً
            $table->float('anomaly_score')->nullable();
            $table->boolean('is_anomaly')->default(false);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_analytics');
    }
};
