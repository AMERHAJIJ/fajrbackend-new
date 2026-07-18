<?php

namespace App\Filament\Resources\RecitationRecordResource\Pages;

use App\Filament\Resources\RecitationRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRecitationRecord extends ViewRecord
{
    protected static string $resource = RecitationRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Düzenle'),
        ];
    }
}
