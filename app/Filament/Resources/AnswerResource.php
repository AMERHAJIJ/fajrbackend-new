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

use Illuminate\Support\Facades\Auth;

class AnswerResource extends Resource
{
    protected static ?string $model = Answer::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    public static function getNavigationLabel(): string { return __('admin.resources.answer.plural_label'); }
    public static function getModelLabel(): string { return __('admin.resources.answer.label'); }
    public static function getPluralModelLabel(): string { return __('admin.resources.answer.plural_label'); }
    public static function getNavigationGroup(): ?string { return __('admin.navigation_group.education_management'); }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('admin.resources.answer.label'))
                    ->schema([
                        Forms\Components\Select::make('question_id')
                            ->label(__('admin.resources.question.label'))
                            ->relationship('question', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\Select::make('quiz_id')
                                    ->label(__('admin.resources.quiz.label'))
                                    ->relationship('quiz', 'title')
                                    ->required(),
                                Forms\Components\TextInput::make('name')
                                    ->label(__('admin.fields.name'))
                                    ->required(),
                                Forms\Components\Toggle::make('active')
                                    ->label(__('admin.fields.active'))
                                    ->default(true),
                            ]),
                        Forms\Components\TextInput::make('title')
                            ->label(__('admin.fields.content'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('isCorrect')
                            ->label(__('admin.fields.is_correct'))
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
                    ->label(__('admin.fields.content'))
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('question.name')
                    ->label(__('admin.resources.question.label'))
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('question.quiz.title')
                    ->label(__('admin.resources.quiz.label'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('isCorrect')
                    ->label(__('admin.fields.is_correct'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Oluşturulma Tarihi')
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
        return Auth::user()->roles->whereIn('name', ['admin', 'teacher'])->isNotEmpty();
    }

    public static function canCreate(): bool
    {
        return Auth::user()->roles->whereIn('name', ['admin', 'teacher'])->isNotEmpty();
    }

    public static function canEdit($record): bool
    {
        return Auth::user()->roles->whereIn('name', ['admin', 'teacher'])->isNotEmpty();
    }

    public static function canDelete($record): bool
    {
        return Auth::user()->roles->contains('name', 'admin');
    }
}
