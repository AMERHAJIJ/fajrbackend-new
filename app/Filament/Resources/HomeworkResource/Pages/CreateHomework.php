<?php

namespace App\Filament\Resources\HomeworkResource\Pages;

use App\Filament\Resources\HomeworkResource;
use Filament\Resources\Pages\CreateRecord;
use App\Services\TeacherTaskAutoTracker;
use Illuminate\Support\Facades\Auth;

class CreateHomework extends CreateRecord
{
    protected static string $resource = HomeworkResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // إذا لم يتم تحديد المعلم، استخدم المستخدم الحالي إذا كان معلماً
        if (empty($data['teacher_id']) && auth()->user()->hasRole('Teacher')) {
            $data['teacher_id'] = auth()->id();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Auto-update teacher task tracker
        $record = $this->record;
        if ($record->teacher_id && $record->subject_id) {
            $teacherId = $record->teacher_id;
            $subjectId = $record->subject_id;
            $date = $record->created_at->toDateString();
            
            TeacherTaskAutoTracker::updateHomeworkTask($teacherId, $subjectId, $date);
        }
    }
}
