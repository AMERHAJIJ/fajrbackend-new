<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomeworkResource\Pages;
use App\Models\Homework;
use App\Models\Subject;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HomeworkResource extends Resource
{
    protected static ?string $model = Homework::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function getNavigationLabel(): string { return __('admin.resources.homework.plural_label'); }
    public static function getModelLabel(): string { return __('admin.resources.homework.label'); }
    public static function getPluralModelLabel(): string { return __('admin.resources.homework.plural_label'); }
    public static function getNavigationGroup(): ?string { return __('admin.navigation_group.education_management'); }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('admin.resources.homework.label'))
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label(__('admin.fields.title'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label(__('admin.fields.benefits'))
                            ->rows(3)
                            ->nullable(),
                        Forms\Components\TextInput::make('lesson_name')
                            ->label(__('admin.fields.lesson_name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('page_number')
                            ->label(__('admin.fields.from_page'))
                            ->nullable()
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make(__('admin.fields.timing'))
                    ->schema([
                        Forms\Components\Select::make('subject_id')
                            ->label(__('admin.fields.subject'))
                            ->relationship('subject', 'title')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->options(function () {
                                $user = auth()->user();
                                if ($user->hasRole('teacher')) {
                                    return $user->subjectsAsTeacher()->pluck('title', 'id');
                                }
                                return \App\Models\Subject::pluck('title', 'id');
                            })
                            ->reactive(),
                        Forms\Components\Select::make('teacher_id')
                            ->label(__('admin.fields.teacher'))
                            ->options(function () {
                                $user = auth()->user();
                                if ($user->hasRole('teacher')) {
                                    return [$user->id => $user->name];
                                }
                                return [null => 'اختر المعلم'] + \App\Models\User::role('teacher')->pluck('name', 'id')->toArray();
                            })
                            ->default(fn () => auth()->user()->hasRole('teacher') ? auth()->id() : null)
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->disabled(fn () => auth()->user()->hasRole('teacher')),
                        Forms\Components\DatePicker::make('due_date')
                            ->label(__('admin.fields.due_date'))
                            ->native(false)
                            ->nullable(),
                        Forms\Components\TextInput::make('max_score')
                            ->label(__('admin.fields.max_score'))
                            ->numeric()
                            ->default(null)
                            ->nullable()
                            ->minValue(1),
                    ])->columns(2),

                Forms\Components\Section::make(__('admin.fields.settings'))
                    ->schema([
                        Forms\Components\Toggle::make('active')
                            ->label(__('admin.fields.active'))
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                Tables\Columns\TextColumn::make('subject.title')
                    ->label('المادة الدراسية')
                    ->searchable()
                    ->sortable(),
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
                Tables\Filters\SelectFilter::make('subject_id')
                    ->label('المادة الدراسية')
                    ->relationship('subject', 'title')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('teacher_id')
                    ->label('Öğretmen')
                    ->relationship('teacher', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('due_date')
                    ->form([
                        Forms\Components\DatePicker::make('due_from')
                            ->label('Başlangıç Tarihi'),
                        Forms\Components\DatePicker::make('due_until')
                            ->label('Bitiş Tarihi'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['due_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('due_date', '>=', $date),
                            )
                            ->when(
                                $data['due_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('due_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                \App\Filament\Resources\HomeworkResource\Actions\ExportHomeworkToWhatsApp::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('due_date', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery();

        // If user is teacher, show only homeworks for their subjects
        if ($user->hasRole('teacher')) {
            $query->whereHas('subject.subjectTeachers', function ($q) use ($user) {
                $q->where('teacher_id', $user->id);
            });
        }

        // If user is student, show only homeworks for their registered subjects
        if ($user->hasRole('student')) {
            $query->whereHas('subject.studentSubjects', function ($q) use ($user) {
                $q->where('student_id', $user->id);
            });
        }
        
        return $query;
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
            'index' => Pages\ListHomeworks::route('/'),
            'create' => Pages\CreateHomework::route('/create'),
            'view' => Pages\ViewHomework::route('/{record}'),
            'edit' => Pages\EditHomework::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view homeworks');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create homeworks');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('edit homeworks');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->can('delete homeworks');
    }
}
