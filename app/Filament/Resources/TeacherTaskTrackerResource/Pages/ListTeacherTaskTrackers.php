<?php

namespace App\Filament\Resources\TeacherTaskTrackerResource\Pages;

use App\Filament\Resources\TeacherTaskTrackerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTeacherTaskTrackers extends ListRecords
{
    protected static string $resource = TeacherTaskTrackerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة تتبع مهام جديد'),
        ];
    }
}
