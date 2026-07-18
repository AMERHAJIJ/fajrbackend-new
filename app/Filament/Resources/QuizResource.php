<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuizResource\Pages;
use App\Filament\Resources\QuizResource\RelationManagers;
use App\Models\Quiz;
use App\Models\Video;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QuizResource extends Resource
{
    protected static ?string $model = Quiz::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';
    public static function getNavigationLabel(): string { return __('admin.resources.quiz.plural_label'); }
    public static function getModelLabel(): string { return __('admin.resources.quiz.label'); }
    public static function getPluralModelLabel(): string { return __('admin.resources.quiz.plural_label'); }
    public static function getNavigationGroup(): ?string { return __('admin.navigation_group.education_management'); }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الاختبار')
                    ->description('أدخل البيانات الأساسية للاختبار هنا')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('عنوان الاختبار')
                            ->placeholder('مثلاً: اختبار الهمزة المتوسطة')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('subject_id')
                            ->label('المادة الدراسية')
                            ->prefixIcon('heroicon-o-book-open')
                            ->relationship('subject', 'title', function (Builder $query) {
                                if (auth()->user()->hasRole('teacher')) {
                                    return $query->whereHas('teachers', function ($q) {
                                        $q->where('users.id', auth()->id());
                                    });
                                }
                                return $query;
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('video_id', null)),
                        Forms\Components\Select::make('teacher_id')
                            ->label('الأستاذ المسؤول')
                            ->prefixIcon('heroicon-o-user')
                            ->relationship('teacher', 'name', function (Builder $query) {
                                return $query->role('teacher');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->visible(fn () => auth()->user()->hasRole('admin')), // يظهر فقط للأدمن
                        Forms\Components\Select::make('video_id')
                            ->label('الفيديو (اختياري)')
                            ->prefixIcon('heroicon-o-video-camera')
                            ->relationship('video', 'name', function (Builder $query, Forms\Get $get) {
                                $subjectId = $get('subject_id');
                                if ($subjectId) {
                                    return $query->where('object_type', 'subject')
                                                 ->where('object_id', $subjectId);
                                }
                                return $query;
                            })
                            ->searchable()
                            ->preload(),
                        Forms\Components\Toggle::make('active')
                            ->label('نشط')
                            ->onIcon('heroicon-m-check')
                            ->offIcon('heroicon-m-x-mark')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('أسئلة الاختبار')
                    ->description('أضف الأسئلة وخيارات الإجابة (يجب أن يكون هناك إجابة واحدة صحيحة على الأقل)')
                    ->icon('heroicon-o-question-mark-circle')
                    ->schema([
                        Forms\Components\Repeater::make('questions')
                            ->relationship()
                            ->label('قائمة الأسئلة')
                            ->schema([
                                Forms\Components\Grid::make(4)
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('نص السؤال')
                                            ->placeholder('اكتب سؤالك هنا...')
                                            ->required()
                                            ->columnSpan(3),
                                        Forms\Components\Toggle::make('active')
                                            ->label('نشط')
                                            ->default(true)
                                            ->columnSpan(1),
                                    ]),
                                
                                Forms\Components\Repeater::make('answers')
                                    ->relationship()
                                    ->label('خيارات الإجابة (يجب إضافة 4 خيارات على الأقل)')
                                    ->schema([
                                        Forms\Components\TextInput::make('title')
                                            ->label('نص الإجابة')
                                            ->required()
                                            ->columnSpan(3),
                                        Forms\Components\Toggle::make('isCorrect')
                                            ->label('Doğru Cevap')
                                            ->onColor('success')
                                            ->live()
                                            ->columnSpan(1)
                                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get, $component) {
                                                if ($state) {
                                                    // هذا الجزء الذكي: إذا تم تفعيل هذا الخيار، نقوم بإلغاء البقية في نفس السؤال
                                                    $container = $component->getContainer()->getParentComponent()->getState();
                                                    foreach ($container as $uuid => $item) {
                                                        if ($uuid !== $component->getContainer()->getStatePath()) {
                                                            $set("../{$uuid}.isCorrect", false);
                                                        }
                                                    }
                                                }
                                            }),
                                    ])
                                    ->columns(4)
                                    ->grid(2)
                                    ->minItems(4) // إجبار المعلم على إضافة 4 خيارات
                                    ->addActionLabel('إضافة خيار إجابة')
                                    ->reorderable(false)
                                    ->collapsible(),
                            ])
                            ->addActionLabel('إضافة سؤال جديد')
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? 'سؤال جديد'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('admin.fields.title'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject.title')
                    ->label(__('admin.fields.subject'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('video.name')
                    ->label(__('admin.fields.video'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('questions_count')
                    ->label(__('admin.fields.questions_count'))
                    ->counts('questions')
                    ->sortable(),
                Tables\Columns\IconColumn::make('active')
                    ->label(__('admin.fields.active'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Oluşturulma Tarihi')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label('نشط')
                    ->boolean()
                    ->trueLabel('نشط فقط')
                    ->falseLabel('غير نشط فقط'),
                Tables\Filters\SelectFilter::make('video_id')
                    ->label('Video')
                    ->relationship('video', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            RelationManagers\StudentQuizzesRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->hasRole('teacher')) {
            return $query->where('teacher_id', auth()->id());
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuizzes::route('/'),
            'create' => Pages\CreateQuiz::route('/create'),
            'view' => Pages\ViewQuiz::route('/{record}'),
            'edit' => Pages\EditQuiz::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return true; // جميع المستخدمين يمكنهم رؤية الاختبارات
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
