<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Checkbox;
use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class Login extends BaseLogin
{
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getRememberFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('اسم المستخدم أو البريد الإلكتروني')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getRememberFormComponent(): Component
    {
        return Checkbox::make('remember')
            ->label('تذكرني')
            ->extraInputAttributes(['tabindex' => 3]);
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $data = $this->form->getState();
            $login = $data['email'];
            $password = $data['password'];
            $remember = $data['remember'] ?? false;

            // محاولة تسجيل الدخول بالبريد الإلكتروني أولاً
            if (Auth::attempt(['email' => $login, 'password' => $password], $remember)) {
                return app(LoginResponse::class);
            }

            // إذا فشل، محاولة تسجيل الدخول باسم المستخدم
            if (Auth::attempt(['username' => $login, 'password' => $password], $remember)) {
                return app(LoginResponse::class);
            }

            // إذا فشل كلاهما، رمي خطأ
            throw ValidationException::withMessages([
                'data.email' => __('filament-panels::pages/auth/login.messages.failed'),
            ]);

        } catch (ValidationException $exception) {
            throw $exception;
        }
    }
}
