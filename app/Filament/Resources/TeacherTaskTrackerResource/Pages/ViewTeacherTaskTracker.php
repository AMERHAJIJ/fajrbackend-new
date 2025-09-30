<?php

namespace App\Filament\Resources\TeacherTaskTrackerResource\Pages;

use App\Filament\Resources\TeacherTaskTrackerResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTeacherTaskTracker extends ViewRecord
{
    protected static string $resource = TeacherTaskTrackerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
