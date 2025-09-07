<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnswerResource\Pages;
use App\Filament\Resources\AnswerResource\RelationManagers;
use App\Models\Answer;
use App\Models\Question;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AnswerResource extends Resource
{
    protected static ?string $model = Answer::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationLabel = 'الإجابات';

    protected static ?string $modelLabel = 'إجابة';

    protected static ?string $pluralModelLabel = 'الإجابات';

    protected static ?string $navigationGroup = 'إدارة الاختبارات';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الإجابة')
                    ->schema([
                        Forms\Components\Select::make('question_id')
                            ->label('السؤال')
                            ->relationship('question', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\Select::make('quiz_id')
                                    ->label('الاختبار')
                                    ->relationship('quiz', 'title')
                                    ->required(),
                                Forms\Components\TextInput::make('name')
                                    ->label('نص السؤال')
                                    ->required(),
                                Forms\Components\Toggle::make('active')
                                    ->label('نشط')
                                    ->default(true),
                            ]),
                        Forms\Components\TextInput::make('title')
                            ->label('نص الإجابة')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('isCorrect')
                            ->label('إجابة صحيحة')
                            ->helperText('حدد هذا الخيار إذا كانت هذه الإجابة صحيحة')
                            ->default(false),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('نص الإجابة')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('question.name')
                    ->label('السؤال')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('question.quiz.title')
                    ->label('الاختبار')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('isCorrect')
                    ->label('إجابة صحيحة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('question_id')
                    ->label('السؤال')
                    ->relationship('question', 'name')
                    ->searchable(),
                Tables\Filters\TernaryFilter::make('isCorrect')
                    ->label('نوع الإجابة')
                    ->placeholder('الكل')
                    ->trueLabel('إجابات صحيحة')
                    ->falseLabel('إجابات خاطئة'),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnswers::route('/'),
            'create' => Pages\CreateAnswer::route('/create'),
            'edit' => Pages\EditAnswer::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view quizzes');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create quizzes');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('edit quizzes');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->can('delete quizzes');
    }
}
