<?php

namespace App\Filament\Resources\SubjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HomeworksRelationManager extends RelationManager
{
    protected static string $relationship = 'homeworks';

    protected static ?string $title = 'Ödevler';

    protected static ?string $modelLabel = 'Ödev';

    protected static ?string $pluralModelLabel = 'Ödevler';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('عنوان الواجب')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('وصف الواجب')
                    ->rows(3)
                    ->nullable(),
                Forms\Components\TextInput::make('lesson_name')
                    ->label('Ders Adı')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('page_number')
                    ->label('رقم الصفحة')
                    ->nullable()
                    ->maxLength(255),
                Forms\Components\Select::make('teacher_id')
                    ->label('المعلم المسؤول')
                    ->relationship('teacher', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->default(auth()->id()),
                Forms\Components\DatePicker::make('due_date')
                    ->label('Teslim Tarihi')
                    ->required()
                    ->native(false),
                Forms\Components\TextInput::make('max_score')
                    ->label('Maksimum Puan')
                    ->numeric()
                    ->default(100)
                    ->required()
                    ->minValue(1),
                Forms\Components\Toggle::make('active')
                    ->label('نشط')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان الواجب')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lesson_name')
                    ->label('Ders Adı')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('page_number')
                    ->label('رقم الصفحة')
                    ->searchable()
                    ->placeholder('غير محدد'),
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Öğretmen')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Teslim Tarihi')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($record) => $record->due_date < now() ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('max_score')
                    ->label('Maksimum Puan')
                    ->sortable(),
                Tables\Columns\IconColumn::make('active')
                    ->label('نشط')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label('نشط')
                    ->boolean()
                    ->trueLabel('نشط فقط')
                    ->falseLabel('غير نشط فقط'),
                Tables\Filters\SelectFilter::make('teacher_id')
                    ->label('Öğretmen')
                    ->relationship('teacher', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['subject_id'] = $this->ownerRecord->id;
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('due_date', 'desc');
    }
}
