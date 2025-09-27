<?php

namespace App\Filament\Resources\RecitationRecordResource\Pages;

use App\Filament\Resources\RecitationRecordResource;
use Filament\Resources\Pages\CreateRecord;
use App\Services\TeacherTaskAutoTracker;
use Illuminate\Support\Facades\Auth;

class CreateRecitationRecord extends CreateRecord
{
    protected static string $resource = RecitationRecordResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        // Auto-update teacher task tracker
        $record = $this->record;
        $teacherId = Auth::id();
        $date = $record->created_at->toDateString();
        
        // Get subject ID from the student's subjects
        if ($teacherId && $record->student) {
            $student = $record->student;
            if ($student->subjectsAsStudent->isNotEmpty()) {
                // Find the subject that the teacher teaches
                $teacherSubjects = $student->subjectsAsStudent->filter(function ($subject) use ($teacherId) {
                    return $subject->teachers->contains('id', $teacherId);
                });
                
                if ($teacherSubjects->isNotEmpty()) {
                    $subjectId = $teacherSubjects->first()->id;
                    TeacherTaskAutoTracker::updateRecitationTask($teacherId, $subjectId, $date);
                }
            }
        }
    }
}
