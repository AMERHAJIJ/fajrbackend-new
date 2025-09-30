<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentAnswerResource\Pages;
use App\Filament\Resources\StudentAnswerResource\RelationManagers;
use App\Models\StudentAnswer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StudentAnswerResource extends Resource
{
    protected static ?string $model = StudentAnswer::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'إجابات الطلاب';

    protected static ?string $modelLabel = 'إجابة طالب';

    protected static ?string $pluralModelLabel = 'إجابات الطلاب';

    protected static ?string $navigationGroup = 'إدارة الاختبارات';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الإجابة')
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->label('الطالب')
                            ->relationship('student', 'name')
                            ->searchable()
                            ->required()
                            ->disabled(),
                        Forms\Components\Select::make('quiz_id')
                            ->label('الاختبار')
                            ->relationship('quiz', 'title')
                            ->searchable()
                            ->required()
                            ->disabled(),
                        Forms\Components\Select::make('question_id')
                            ->label('السؤال')
                            ->relationship('question', 'name')
                            ->searchable()
                            ->required()
                            ->disabled(),
                        Forms\Components\Select::make('answer_id')
                            ->label('الإجابة المختارة')
                            ->relationship('answer', 'title')
                            ->searchable()
                            ->required()
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label('الطالب')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quiz.title')
                    ->label('الاختبار')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('question.name')
                    ->label('السؤال')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('answer.title')
                    ->label('الإجابة المختارة')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\IconColumn::make('answer.isCorrect')
                    ->label('صحيحة؟')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإجابة')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('student_id')
                    ->label('الطالب')
                    ->relationship('student', 'name')
                    ->searchable(),
                Tables\Filters\SelectFilter::make('quiz_id')
                    ->label('الاختبار')
                    ->relationship('quiz', 'title')
                    ->searchable(),
                Tables\Filters\TernaryFilter::make('answer.isCorrect')
                    ->label('نوع الإجابة')
                    ->placeholder('الكل')
                    ->trueLabel('إجابات صحيحة')
                    ->falseLabel('إجابات خاطئة'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudentAnswers::route('/'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view quizzes');
    }

    public static function canCreate(): bool
    {
        return false; // لا يمكن إنشاء إجابات طلاب يدوياً
    }

    public static function canEdit($record): bool
    {
        return false; // لا يمكن تعديل إجابات الطلاب
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->hasRole('admin'); // المدير فقط يمكنه الحذف
    }
}
