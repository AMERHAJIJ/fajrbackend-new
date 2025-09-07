<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateAttendance extends CreateRecord
{
    protected static string $resource = AttendanceResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $subjectId = $data['subject_id'];
        $date = $data['date'];
        $presentStudentIds = $data['present_students'] ?? [];

        // حذف السجلات الموجودة لنفس التاريخ والمادة
        Attendance::where('subject_id', $subjectId)
            ->whereDate('date', $date)
            ->delete();

        // الحصول على جميع طلاب المادة
        $allStudents = User::role('student')
            ->whereHas('subjectsAsStudent', function ($query) use ($subjectId) {
                $query->where('subject_id', $subjectId);
            })
            ->pluck('id');
            
        \Log::info("عدد الطلاب في المادة $subjectId: " . count($allStudents));
        \Log::info("معرفات الطلاب: " . implode(', ', $allStudents->toArray()));
        \Log::info("الطلاب الحاضرين: " . implode(', ', $presentStudentIds));

        // إنشاء سجلات الحضور مع تسجيل الأخطاء
        foreach ($allStudents as $studentId) {
            try {
                $attendance = Attendance::create([
                    'student_id' => $studentId,
                    'subject_id' => $subjectId,
                    'date' => $date,
                    'status' => in_array($studentId, $presentStudentIds), // حاضر إذا كان في القائمة
                ]);
                \Log::info("تم حفظ حضور الطالب ID: $studentId");
            } catch (\Exception $e) {
                \Log::error("خطأ في حفظ حضور الطالب ID: $studentId - " . $e->getMessage());
            }
        }

        // إرجاع أول سجل تم إنشاؤه للعرض
        return Attendance::where('subject_id', $subjectId)
            ->whereDate('date', $date)
            ->first();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
