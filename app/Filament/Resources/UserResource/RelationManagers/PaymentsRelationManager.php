<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';
    
    protected static ?string $title = 'المدفوعات والأقساط';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('payment_type')
                    ->label('Ödeme Türü')
                    ->options([
                        'course' => 'رسوم الدورة',
                        'bus' => 'اشتراك الباص',
                    ])
                    ->reactive()
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->label('Tutar')
                    ->required()
                    ->numeric()
                    ->hint(function (callable $get, $livewire) {
                        $type = $get('payment_type');
                        if (!$type) return null;
                        
                        $user = $livewire->ownerRecord;
                        if ($type === 'course') {
                            return 'المتبقي من الدورة: ' . $user->remaining_course_fee;
                        } else {
                            return 'المتبقي من الباص: ' . $user->remaining_bus_fee;
                        }
                    }),
                Forms\Components\DatePicker::make('payment_date')
                    ->label('Ödeme Tarihi')
                    ->default(now())
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->label('Notlar')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('amount')
            ->columns([
                Tables\Columns\TextColumn::make('amount')
                    ->label('Tutar')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_type')
                    ->label('Ödeme Türü')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'course' => 'رسوم الدورة',
                        'bus' => 'اشتراك الباص',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'course' => 'success',
                        'bus' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Ödeme Tarihi')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Notlar')
                    ->limit(50),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة دفعة جديدة'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
