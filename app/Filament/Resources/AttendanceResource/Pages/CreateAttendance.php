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
        $record = static::getResource()::handleRecordCreation($data);

        // Auto-update teacher task tracker
        $subjectId = $data['subject_id'] ?? null;
        $date = $data['date'] ?? null;
        if ($subjectId && $date) {
            $teacherId = Auth::id();
            if ($teacherId) {
                TeacherTaskAutoTracker::updateAttendanceTask($teacherId, $subjectId, $date);
            }
        }

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
