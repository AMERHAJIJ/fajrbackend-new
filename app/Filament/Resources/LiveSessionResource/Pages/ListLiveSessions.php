<?php

namespace App\Filament\Resources\LiveSessionResource\Pages;

use App\Filament\Resources\LiveSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLiveSessions extends ListRecords
{
    protected static string $resource = LiveSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('authorize_google')
                ->label('🔑 ربط Google Meet')
                ->color('warning')
                ->icon('heroicon-o-link')
                ->url(route('google.oauth.redirect'))
                ->visible(fn () => !app(\App\Services\GoogleMeetService::class)->isAuthorized()),

            Actions\CreateAction::make()
                ->label('إنشاء جلسة'),
        ];
    }
}
