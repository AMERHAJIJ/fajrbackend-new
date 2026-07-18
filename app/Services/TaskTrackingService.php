<?php

namespace App\Services;

use App\Models\TeacherTaskTracker;
use Carbon\Carbon;

class TaskTrackingService
{
    /**
     * تحديث حالة مهمة معينة للأستاذ اليوم.
     */
    public static function track($teacherId, $subjectId, string $taskColumn)
    {
        if (!$teacherId || !$subjectId) {
            return;
        }

        TeacherTaskTracker::updateOrCreate(
            [
                'teacher_id' => $teacherId,
                'subject_id' => $subjectId,
                'date' => Carbon::today(),
            ],
            [
                $taskColumn => true,
            ]
        );
    }
}
