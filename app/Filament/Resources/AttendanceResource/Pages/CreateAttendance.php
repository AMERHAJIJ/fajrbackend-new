<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\TeacherTaskAutoTracker;

class CreateAttendance extends CreateRecord
{
    protected static string $resource = AttendanceResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $attendanceStatus = $data['attendance_status'] ?? [];
        $selectedStudents = $data['selected_students'] ?? [];
        $subjectId = $data['subject_id'];
        $date = $data['date'];

        // تسجيل البيانات للتأكد
        Log::info('Attendance Data:', [
            'attendance_status' => $attendanceStatus,
            'selected_students' => $selectedStudents,
            'subject_id' => $subjectId,
            'date' => $date
        ]);

        // Make sure we have attendance status for all selected students
        foreach ($selectedStudents as $studentId) {
            if (!array_key_exists($studentId, $attendanceStatus)) {
                $attendanceStatus[$studentId] = true; // Default to present
            }
            // Ensure the status is a boolean
            $attendanceStatus[$studentId] = (bool)($attendanceStatus[$studentId] ?? true);
        }

        $attendances = [];

        // Process each student's attendance
        foreach ($attendanceStatus as $studentId => $status) {
            if (!in_array($studentId, $selectedStudents)) {
                continue; // Skip if student is not in the selected students
            }

            // Check if attendance already exists for this student, subject, and date
            $existing = Attendance::where('student_id', $studentId)
                ->where('subject_id', $subjectId)
                ->whereDate('date', $date)
                ->first();

            if ($existing) {
                // Update existing record
                $existing->update(['status' => $status]);
                $attendances[] = $existing;
            } else {
                // Create new record
                $attendances[] = Attendance::create([
                    'student_id' => $studentId,
                    'subject_id' => $subjectId,
                    'date' => $date,
                    'status' => $status,
                ]);
            }
        }

        // Auto-update teacher task tracker
        if (!empty($attendances) && $subjectId) {
            $teacherId = Auth::id();
            if ($teacherId) {
                TeacherTaskAutoTracker::updateAttendanceTask($teacherId, $subjectId, $date);
            }
        }

        // Return the first attendance record (for Filament)
        return $attendances[0] ?? new Attendance($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
