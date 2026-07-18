<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VideoAnalytic;
use App\Models\User;
use App\Models\Video;

class VideoAnalyticSeeder extends Seeder
{
    public function run(): void
    {
        // نجلب أول 10 طلاب
        $students = User::limit(10)->get();
        
        // نجلب أول فيديو متاح
        $video = Video::first();
        
        if (!$video || $students->isEmpty()) {
            return;
        }

        foreach ($students as $index => $student) {
            
            // هب طالب رقم 9 و 10 هما "الشاذين" (Anomalies)
            $isAnomaly = ($index >= 8); 

            if ($isAnomaly) {
                // بيانات طالب "مريب" (غشاش)
                VideoAnalytic::create([
                    'student_id' => $student->id,
                    'video_id' => $video->id,
                    'watched_duration' => 60, // شاهد دقيقة واحدة فقط
                    'total_video_duration' => 600, // من أصل 10 دقائق
                    'pause_count' => 15, // توقفات كثيرة جداً ومزعجة
                    'forward_skip_count' => 20, // قفزات كثيرة للأمام
                    'backward_skip_count' => 0,
                    'playback_rate' => 2.0, // سرعة مضاعفة جداً
                    'app_switch_count' => 12, // خرج من التطبيق كثيراً
                    'is_completed' => true, // يدعي الإكمال
                    'total_session_time' => 120, // وقت الجلسة قصير جداً
                    'is_anomaly' => false,
                ]);
            } else {
                // بيانات طالب "طبيعي" (مجتهد)
                VideoAnalytic::create([
                    'student_id' => $student->id,
                    'video_id' => $video->id,
                    'watched_duration' => rand(550, 600), // شاهد الفيديو كاملاً تقريباً
                    'total_video_duration' => 600,
                    'pause_count' => rand(1, 3),
                    'forward_skip_count' => rand(0, 2),
                    'backward_skip_count' => rand(1, 4),
                    'playback_rate' => 1.0,
                    'app_switch_count' => rand(0, 2),
                    'is_completed' => true,
                    'total_session_time' => rand(580, 650),
                    'is_anomaly' => false,
                ]);
            }
        }
    }
}
