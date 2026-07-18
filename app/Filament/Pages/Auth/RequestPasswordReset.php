<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;

class RequestPasswordReset extends BaseRequestPasswordReset
{
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getEmailFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('E-posta')
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }
}
