<?php

namespace App\Services;

use App\Models\TeacherTaskTracker;
use App\Models\User;
use App\Models\Subject;
use App\Models\Attendance;
use App\Models\RecitationRecord;
use App\Models\Homework;
use App\Models\NextRecitation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TeacherTaskAutoTracker
{
    /**
     * تحديث تلقائي لمهام المعلم عند تسجيل الحضور
     */
    public static function updateAttendanceTask(int $teacherId, int $subjectId, string $date): void
    {
        try {
            $tracker = self::getOrCreateTracker($teacherId, $subjectId, $date);
            $tracker->update(['attendance_taken' => true]);
            
            Log::info("Auto-updated attendance task for teacher {$teacherId}, subject {$subjectId}, date {$date}");
        } catch (\Exception $e) {
            Log::error("Failed to auto-update attendance task: " . $e->getMessage());
        }
    }

    /**
     * تحديث تلقائي لمهام المعلم عند تسجيل التلاوة
     */
    public static function updateRecitationTask(int $teacherId, int $subjectId, string $date): void
    {
        try {
            $tracker = self::getOrCreateTracker($teacherId, $subjectId, $date);
            $tracker->update(['recitation_recorded' => true]);
            
            Log::info("Auto-updated recitation task for teacher {$teacherId}, subject {$subjectId}, date {$date}");
        } catch (\Exception $e) {
            Log::error("Failed to auto-update recitation task: " . $e->getMessage());
        }
    }

    /**
     * تحديث تلقائي لمهام المعلم عند تحديد التلاوة التالية
     */
    public static function updateNextRecitationTask(int $teacherId, int $subjectId, string $date): void
    {
        try {
            $tracker = self::getOrCreateTracker($teacherId, $subjectId, $date);
            $tracker->update(['next_recitation_set' => true]);
            
            Log::info("Auto-updated next recitation task for teacher {$teacherId}, subject {$subjectId}, date {$date}");
        } catch (\Exception $e) {
            Log::error("Failed to auto-update next recitation task: " . $e->getMessage());
        }
    }

    /**
     * تحديث تلقائي لمهام المعلم عند إرسال الواجبات
     */
    public static function updateHomeworkTask(int $teacherId, int $subjectId, string $date): void
    {
        try {
            $tracker = self::getOrCreateTracker($teacherId, $subjectId, $date);
            $tracker->update(['homework_sent' => true]);
            
            Log::info("Auto-updated homework task for teacher {$teacherId}, subject {$subjectId}, date {$date}");
        } catch (\Exception $e) {
            Log::error("Failed to auto-update homework task: " . $e->getMessage());
        }
    }

    /**
     * تحديث تلقائي لمهام المعلم عند إرسال الواتساب
     */
    public static function updateWhatsAppTask(int $teacherId, int $subjectId, string $date): void
    {
        try {
            $tracker = self::getOrCreateTracker($teacherId, $subjectId, $date);
            $tracker->update(['whatsapp_sent' => true]);
            
            Log::info("Auto-updated WhatsApp task for teacher {$teacherId}, subject {$subjectId}, date {$date}");
        } catch (\Exception $e) {
            Log::error("Failed to auto-update WhatsApp task: " . $e->getMessage());
        }
    }

    /**
     * الحصول على أو إنشاء سجل تتبع المهام
     */
    private static function getOrCreateTracker(int $teacherId, int $subjectId, string $date): TeacherTaskTracker
    {
        try {
            return TeacherTaskTracker::firstOrCreate(
                [
                    'teacher_id' => $teacherId,
                    'subject_id' => $subjectId,
                    'date' => $date,
                ],
                [
                    'attendance_taken' => false,
                    'recitation_recorded' => false,
                    'next_recitation_set' => false,
                    'whatsapp_sent' => false,
                    'homework_sent' => false,
                ]
            );
        } catch (\Exception $e) {
            Log::error("Failed to get or create tracker: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * تحديث تلقائي لجميع المهام بناءً على البيانات الموجودة
     */
    public static function syncAllTasks(): void
    {
        try {
            $today = Carbon::today()->toDateString();
            
            // تحديث مهام الحضور
            $attendanceRecords = Attendance::with('subject.teachers')->whereDate('date', $today)->get();
            foreach ($attendanceRecords as $attendance) {
                $teacher = $attendance->subject->teachers->first();
                if ($teacher) {
                    self::updateAttendanceTask($teacher->id, $attendance->subject_id, $today);
                }
            }

            // تحديث مهام التلاوة
            $recitationRecords = RecitationRecord::with('subject.teachers')->whereDate('created_at', $today)->get();
            foreach ($recitationRecords as $recitation) {
                $teacher = $recitation->subject->teachers->first();
                if ($teacher) {
                    self::updateRecitationTask($teacher->id, $recitation->subject_id, $today);
                }
            }

            // تحديث مهام الواجبات
            $homeworkRecords = Homework::whereDate('created_at', $today)->get();
            foreach ($homeworkRecords as $homework) {
                if ($homework->teacher_id) {
                    self::updateHomeworkTask($homework->teacher_id, $homework->subject_id, $today);
                }
            }

            // تحديث مهام التلاوة التالية
            $nextRecitationRecords = NextRecitation::with('student.subjectsAsStudent.teachers')->whereDate('created_at', $today)->get();
            foreach ($nextRecitationRecords as $nextRecitation) {
                $student = $nextRecitation->student;
                if ($student && $student->subjectsAsStudent->isNotEmpty()) {
                    $subject = $student->subjectsAsStudent->first();
                    $teacher = $subject->teachers->first();
                    if ($teacher) {
                        self::updateNextRecitationTask($teacher->id, $subject->id, $today);
                    }
                }
            }

            Log::info("Synced all teacher tasks for date: {$today}");
        } catch (\Exception $e) {
            Log::error("Failed to sync all tasks: " . $e->getMessage());
        }
    }

    /**
     * الحصول على تقرير المهام المفقودة
     */
    public static function getMissingTasksReport(string $date = null): array
    {
        $date = $date ?? Carbon::today()->toDateString();
        
        $trackers = TeacherTaskTracker::whereDate('date', $date)
            ->with(['teacher', 'subject'])
            ->get();

        $missingTasks = [];

        foreach ($trackers as $tracker) {
            $missing = [];
            
            if (!$tracker->attendance_taken) $missing[] = 'الحضور';
            if (!$tracker->recitation_recorded) $missing[] = 'التلاوة';
            if (!$tracker->next_recitation_set) $missing[] = 'التلاوة التالية';
            if (!$tracker->homework_sent) $missing[] = 'الواجبات';
            if (!$tracker->whatsapp_sent) $missing[] = 'الواتساب';

            if (!empty($missing)) {
                $missingTasks[] = [
                    'teacher' => $tracker->teacher->name,
                    'subject' => $tracker->subject->title,
                    'missing_tasks' => $missing,
                    'completion_rate' => $tracker->completion_percentage,
                ];
            }
        }

        return $missingTasks;
    }
}
