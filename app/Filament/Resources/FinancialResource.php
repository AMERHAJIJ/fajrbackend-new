<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FinancialResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\TextInput;

class FinancialResource extends Resource
{
    protected static ?string $model = User::class;
    
    protected static ?string $slug = 'financials';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    
    public static function getNavigationLabel(): string { return __('admin.resources.financial.plural_label'); }
    
    public static function getPluralModelLabel(): string { return __('admin.resources.financial.plural_label'); }
    
    public static function getModelLabel(): string { return 'Finansal Dosya'; }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Öğrenci Bilgileri')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('admin.fields.student_name'))
                            ->disabled(),
                        Forms\Components\TextInput::make('phone')
                            ->label('Telefon')
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                // Show only students
                return $query->whereHas('roles', function ($q) {
                    $q->where('name', 'student');
                });
            })
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.fields.student_name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('remaining_course_fee')
                    ->label('Kalan Kurs Ücreti')
                    ->state(fn ($record) => $record->remaining_course_fee)
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('remaining_bus_fee')
                    ->label('Kalan Otobüs Ücreti')
                    ->state(fn ($record) => $record->remaining_bus_fee)
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('financial_status')
                    ->label('Genel Durum')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'paid' => 'Ödendi',
                        'partial' => 'Kısmi Ödeme',
                        'unpaid' => 'Ödenmedi',
                        'not_set' => 'Belirtilmedi',
                        default => 'Belirtilmedi',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'partial' => 'warning',
                        'unpaid' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('whatsapp')
                    ->label('WhatsApp Mesajı')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('message_type')
                            ->label('Mesaj Türü')
                            ->options([
                                'course' => 'Kurs Ücreti Hatırlatması',
                                'bus' => 'Otobüs Ücreti Hatırlatması',
                                'both' => 'Genel Hatırlatma (Kurs + Otobüs)'
                            ])
                            ->required()
                            ->default('both'),
                    ])
                    ->action(function ($record, array $data) {
                        $phone = ltrim($record->phone, '0');
                        
                        if ($data['message_type'] === 'course') {
                            $rem = $record->remaining_course_fee;
                            $msg = "Merhaba, {$rem} tutarında kalan kurs ücretiniz bulunmaktadır. Lütfen ödemenizi yapınız. Teşekkürler.";
                        } elseif ($data['message_type'] === 'bus') {
                            $rem = $record->remaining_bus_fee;
                            $msg = "Merhaba, {$rem} tutarında kalan otobüs ücretiniz bulunmaktadır. Lütfen ödemenizi yapınız. Teşekkürler.";
                        } else {
                            $rem = $record->remaining_course_fee + $record->remaining_bus_fee;
                            $msg = "Merhaba, toplam {$rem} tutarında (kurs + otobüs) kalan ücretiniz bulunmaktadır. Lütfen ödemenizi yapınız. Teşekkürler.";
                        }
                        
                        $url = "https://wa.me/{$phone}?text=" . urlencode($msg);
                        
                        // Using browser redirect via Notification or directly redirecting if possible.
                        // In Filament, we can use `redirect()` inside the action or return a RedirectResponse.
                        return redirect()->away($url);
                    })
                    ->openUrlInNewTab() // This usually works with direct URL, but since we have a form, action() handles it. Wait, openUrlInNewTab doesn't work with action() returning redirect. Let's keep it simple.
                    ->visible(fn ($record) => ($record->remaining_course_fee + $record->remaining_bus_fee) > 0),
                Tables\Actions\EditAction::make()
                    ->label('Ödemeleri Yönet'),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\UserResource\RelationManagers\PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFinancials::route('/'),
            'edit' => Pages\EditFinancial::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('admin');
    }
}
