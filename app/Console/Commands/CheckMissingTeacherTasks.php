<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TeacherTaskAutoTracker;
use App\Models\User;
use Carbon\Carbon;

class CheckMissingTeacherTasks extends Command
{
    protected $signature = 'teacher-tasks:check {--date=}';
    protected $description = 'فحص المهام المفقودة للمعلمين';

    public function handle()
    {
        $date = $this->option('date') ?? Carbon::today()->toDateString();
        
        $this->info("فحص المهام المفقودة لتاريخ: {$date}");
        
        // تحديث جميع المهام بناءً على البيانات الموجودة
        TeacherTaskAutoTracker::syncAllTasks();
        
        // الحصول على تقرير المهام المفقودة
        $missingTasks = TeacherTaskAutoTracker::getMissingTasksReport($date);
        
        if (empty($missingTasks)) {
            $this->info("✅ جميع المعلمين أكملوا مهامهم لهذا اليوم!");
            return;
        }
        
        $this->warn("❌ يوجد معلمين لم يكملوا مهامهم:");
        
        foreach ($missingTasks as $task) {
            $this->line("📋 المعلم: {$task['teacher']}");
            $this->line("📚 المادة: {$task['subject']}");
            $this->line("❌ المهام المفقودة: " . implode(', ', $task['missing_tasks']));
            $this->line("📊 نسبة الإنجاز: {$task['completion_rate']}%");
            $this->line("---");
        }
        
        // إرسال إشعارات للمعلمين (يمكن تطويرها لاحقاً)
        $this->info("💡 يمكن إضافة نظام إشعارات للمعلمين هنا");
    }
}
