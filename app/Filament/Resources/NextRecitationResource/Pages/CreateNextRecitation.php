<?php

namespace App\Filament\Resources\NextRecitationResource\Pages;

use App\Filament\Resources\NextRecitationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Services\TeacherTaskAutoTracker;
use Illuminate\Support\Facades\Auth;

class CreateNextRecitation extends CreateRecord
{
    protected static string $resource = NextRecitationResource::class;

    protected function afterCreate(): void
    {
        // Auto-update teacher task tracker for Next Recitation
        $record = $this->record;
        $teacherId = Auth::id();
        $date = $record->created_at->toDateString();
        
        // Get subject ID from student's subjects
        $student = $record->student;
        if ($student && $student->subjectsAsStudent->isNotEmpty() && $teacherId) {
            // Find the subject that the teacher teaches
            $teacherSubjects = $student->subjectsAsStudent->filter(function ($subject) use ($teacherId) {
                return $subject->teachers->contains('id', $teacherId);
            });
            
            if ($teacherSubjects->isNotEmpty()) {
                $subjectId = $teacherSubjects->first()->id;
                TeacherTaskAutoTracker::updateNextRecitationTask($teacherId, $subjectId, $date);
            }
        }
    }
}
