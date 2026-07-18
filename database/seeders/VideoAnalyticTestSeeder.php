<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VideoAnalytic;
use App\Models\User;
use App\Models\Video;

class VideoAnalyticTestSeeder extends Seeder
{
    public function run()
    {
        $student = User::where('username', 'student')->first();
        $video = Video::first();

        if (!$student || !$video) {
            $this->command->error('No student or video found to attach analytics.');
            return;
        }

        // 1. سلوك طبيعي جداً (طالب مركز)
        VideoAnalytic::create([
            'student_id' => $student->id,
            'video_id' => $video->id,
            'watched_duration' => 600, // 10 دقائق
            'pause_count' => 1,
            'forward_skip_count' => 0,
            'backward_skip_count' => 2,
            'playback_rate' => 1.0,
            'app_switch_count' => 0,
        ]);

        // 2. سلوك مشبوه (غشاش أو مهمل)
        VideoAnalytic::create([
            'student_id' => $student->id,
            'video_id' => $video->id,
            'watched_duration' => 120, // دقيقتين فقط
            'pause_count' => 15, // يوقف الفيديو كثيراً
            'forward_skip_count' => 10, // يقدم الفيديو لينهيه بسرعة
            'backward_skip_count' => 0,
            'playback_rate' => 2.0, // سرعة مضاعفة
            'app_switch_count' => 5, // خرج من التطبيق 5 مرات للبحث عن إجابات
        ]);

        $this->command->info('Test analytics added successfully!');
    }
}
