<?php

namespace App\Filament\Resources\TeacherTaskTrackerResource\Pages;

use App\Filament\Resources\TeacherTaskTrackerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTeacherTaskTracker extends EditRecord
{
    protected static string $resource = TeacherTaskTrackerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
