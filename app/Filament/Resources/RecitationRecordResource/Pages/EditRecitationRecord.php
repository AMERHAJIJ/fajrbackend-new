<?php

namespace App\Filament\Resources\RecitationRecordResource\Pages;

use App\Filament\Resources\RecitationRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRecitationRecord extends EditRecord
{
    protected static string $resource = RecitationRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('عرض'),
            Actions\DeleteAction::make()
                ->label('حذف'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
