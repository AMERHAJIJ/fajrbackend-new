<?php

namespace App\Filament\Resources\QuizResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StudentQuizzesRelationManager extends RelationManager
{
    protected static string $relationship = 'studentQuizzes';

    protected static ?string $title = 'نتائج الطلاب';

    protected static ?string $modelLabel = 'نتيجة طالب';

    protected static ?string $pluralModelLabel = 'نتائج الطلاب';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('student_id')
                    ->label('الطالب')
                    ->relationship('student', 'name')
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('score')
                    ->label('النتيجة')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%'),
                Forms\Components\TextInput::make('time_taken')
                    ->label('الوقت المستغرق (بالدقائق)')
                    ->numeric()
                    ->minValue(0),
                Forms\Components\DateTimePicker::make('completed_at')
                    ->label('تاريخ الإكمال')
                    ->default(now()),
                Forms\Components\Toggle::make('passed')
                    ->label('نجح')
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('student.name')
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label('الطالب')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.email')
                    ->label('البريد الإلكتروني')
                    ->searchable(),
                Tables\Columns\TextColumn::make('score')
                    ->label('النتيجة')
                    ->suffix('%')
                    ->sortable()
                    ->color(fn ($state) => $state >= 60 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('time_taken')
                    ->label('الوقت (دقيقة)')
                    ->sortable(),
                Tables\Columns\IconColumn::make('passed')
                    ->label('النجاح')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('تاريخ الإكمال')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('passed')
                    ->label('النجاح')
                    ->boolean()
                    ->trueLabel('ناجح فقط')
                    ->falseLabel('راسب فقط'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
            ->defaultSort('completed_at', 'desc');
    }
}
