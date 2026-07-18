<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Actions\Action;
use App\Models\Setting;
use Filament\Notifications\Notification;

class FinancialSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog';
    public static function getNavigationLabel(): string { return __('admin.navigation_group.settings'); }
    
    public function getTitle(): string | \Illuminate\Contracts\Support\Htmlable { return __('admin.pages.financial_settings.title'); }
    protected static ?string $navigationGroup = 'Sistem Yönetimi';
    protected static ?int $navigationSort = 100;

    protected static string $view = 'filament.pages.financial-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'course_fee' => Setting::getVal('course_fee', 0),
            'bus_fee' => Setting::getVal('bus_fee', 0),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Tüm Öğrenciler İçin Genel Ücretler')
                    ->schema([
                        TextInput::make('course_fee')
                            ->label('Kurs Ücreti')
                            ->numeric()
                            ->required(),
                        TextInput::make('bus_fee')
                            ->label('Otobüs Ücreti')
                            ->numeric()
                            ->required(),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save()
    {
        $data = $this->form->getState();

        Setting::setVal('course_fee', $data['course_fee']);
        Setting::setVal('bus_fee', $data['bus_fee']);

        Notification::make()
            ->title('Başarıyla Kaydedildi')
            ->success()
            ->send();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole('admin');
    }
}
