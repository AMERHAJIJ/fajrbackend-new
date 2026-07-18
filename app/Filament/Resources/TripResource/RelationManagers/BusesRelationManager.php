<?php

namespace App\Filament\Resources\TripResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class BusesRelationManager extends RelationManager
{
    protected static string $relationship = 'buses';
    
    protected static ?string $title = 'إدارة الباصات';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('اسم الباص / المجموعة')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('capacity')
                    ->label('السعة الاستيعابية')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('driver_info')
                    ->label('معلومات السائق')
                    ->maxLength(255),
                Forms\Components\TextInput::make('supervisor_info')
                    ->label('معلومات المشرف المرافق')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الباص')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('capacity')
                    ->label('السعة')
                    ->numeric(),
                Tables\Columns\TextColumn::make('participants_count')
                    ->label('الركاب حالياً')
                    ->counts('participants'),
                Tables\Columns\TextColumn::make('empty_seats')
                    ->label('المقاعد الشاغرة')
                    ->state(fn ($record) => $record->empty_seats)
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('driver_info')
                    ->label('السائق')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('supervisor_info')
                    ->label('المشرف')
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة باص'),
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
