<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Auth\Register as BaseRegister;

class Register extends BaseRegister
{
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getUserNameFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                        $this->getBirthdayFormComponent(),
                        $this->getPhoneFormComponent(),
                        $this->getAddressFormComponent(),
                        $this->getImageFormComponent(),
                        $this->getActiveFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getUserNameFormComponent(): Component
    {
        return TextInput::make('username')
            ->label('Kullanıcı Adı')
            ->required()
            ->unique('users', 'username')
            ->maxLength(255);
    }

    protected function getBirthdayFormComponent(): Component
    {
        return DatePicker::make('birthday')
            ->label('Doğum Tarihi')
            ->required()
            ->maxDate(now()->subYears(5));
    }

    protected function getPhoneFormComponent(): Component
    {
        return TextInput::make('phone')
            ->label('Telefon Numarası')
            ->required()
            ->tel()
            ->maxLength(20);
    }

    protected function getAddressFormComponent(): Component
    {
        return TextInput::make('address')
            ->label('Adres')
            ->required()
            ->maxLength(500);
    }

    protected function getImageFormComponent(): Component
    {
        return FileUpload::make('image')
            ->label('الصورة الشخصية')
            ->image()
            ->imageEditor()
            ->directory('users')
            ->nullable();
    }

    protected function getActiveFormComponent(): Component
    {
        return Toggle::make('active')
            ->label('نشط')
            ->default(true)
            ->disabled();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['active'] = true;
        
        return $data;
    }
}
