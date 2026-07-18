<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Filament\Resources\QuestionResource\RelationManagers;
use App\Models\Question;
use App\Models\Quiz;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    public static function getNavigationLabel(): string { return __('admin.resources.question.plural_label'); }
    public static function getModelLabel(): string { return __('admin.resources.question.label'); }
    public static function getPluralModelLabel(): string { return __('admin.resources.question.plural_label'); }
    public static function getNavigationGroup(): ?string { return __('admin.navigation_group.education_management'); }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات السؤال')
                    ->schema([
                        Forms\Components\Select::make('quiz_id')
                            ->label('الاختبار')
                            ->relationship('quiz', 'title')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('title')
                                    ->label(__('admin.fields.title'))
                                    ->required(),
                                Forms\Components\Select::make('video_id')
                                    ->label(__('admin.resources.video.label'))
                                    ->relationship('video', 'name')
                                    ->searchable()
                                    ->required(),
                            ]),
                        Forms\Components\Textarea::make('content')
                            ->label(__('admin.fields.content'))
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('score')
                            ->label(__('admin.fields.score'))
                            ->numeric()
                            ->default(1)
                            ->required(),
                        Forms\Components\Toggle::make('active')
                            ->label(__('admin.fields.active'))
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('quiz.title')
                    ->label(__('admin.resources.quiz.label'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('content')
                    ->label(__('admin.fields.content'))
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('score')
                    ->label(__('admin.fields.score'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('quiz.video.name')
                    ->label(__('admin.resources.video.label'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('active')
                    ->label(__('admin.fields.active'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('answers_count')
                    ->label(__('admin.resources.answer.plural_label'))
                    ->counts('answers')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Oluşturulma Tarihi')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('quiz_id')
                    ->label('الاختبار')
                    ->relationship('quiz', 'title')
                    ->searchable(),
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Durum')
                    ->placeholder('الكل')
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                \App\Filament\Resources\QuestionResource\Actions\ExportToWhatsAppAction::make(),
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
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
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
