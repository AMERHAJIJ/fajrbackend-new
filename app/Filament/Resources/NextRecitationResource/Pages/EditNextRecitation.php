<?php

namespace App\Filament\Resources\NextRecitationResource\Pages;

use App\Filament\Resources\NextRecitationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNextRecitation extends EditRecord
{
    protected static string $resource = NextRecitationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
