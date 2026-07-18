<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubjectResource\Pages;
use App\Filament\Resources\SubjectResource\RelationManagers;
use App\Models\Subject;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    public static function getNavigationLabel(): string { return __('admin.resources.subject.plural_label'); }
    public static function getModelLabel(): string { return __('admin.resources.subject.label'); }
    public static function getPluralModelLabel(): string { return __('admin.resources.subject.plural_label'); }
    public static function getNavigationGroup(): ?string { return __('admin.navigation_group.education_management'); }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('admin.resources.subject.label'))
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label(__('admin.fields.title'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label(__('admin.fields.description'))
                            ->maxLength(1000)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('active')
                            ->label(__('admin.fields.active'))
                            ->default(true),
                        Forms\Components\Toggle::make('is_quran')
                            ->label(__('admin.fields.is_quran'))
                            ->helperText('تفعيل هذا الخيار يجعل النظام يتعرف على هذه المادة في قسم التلاوة والتتبع التلقائي')
                            ->default(false),
                    ])->columns(2),

                Forms\Components\Section::make(__('admin.resources.user.teachers'))
                    ->schema([
                        Forms\Components\CheckboxList::make('teachers')
                            ->label('المعلمين المسؤولين')
                            ->relationship('teachers', 'name')
                            ->options(
                                User::role('teacher')->pluck('name', 'id')
                            )
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(2),
                    ]),

                Forms\Components\Section::make(__('admin.resources.user.students'))
                    ->schema([
                        Forms\Components\CheckboxList::make('students')
                            ->label('الطلاب المسجلين')
                            ->relationship('students', 'name')
                            ->options(
                                User::role('student')->pluck('name', 'id')
                            )
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(2),
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
                Tables\Columns\TextColumn::make('description')
                    ->label(__('admin.fields.description'))
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('teachers_count')
                    ->label('عدد المعلمين')
                    ->counts('teachers')
                    ->sortable(),
                Tables\Columns\TextColumn::make('students_count')
                    ->label('عدد الطلاب')
                    ->counts('students')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_quran')
                    ->label(__('admin.fields.is_quran'))
                    ->boolean()
                    ->trueIcon('heroicon-o-book-open')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('success')
                    ->sortable(),
                Tables\Columns\IconColumn::make('active')
                    ->label('نشطة')
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
                    ->label('نشطة')
                    ->boolean()
                    ->trueLabel('نشطة فقط')
                    ->falseLabel('غير نشطة فقط'),
                Tables\Filters\Filter::make('has_teachers')
                    ->label('لديها معلمين')
                    ->query(fn (Builder $query): Builder => $query->has('teachers')),
                Tables\Filters\Filter::make('has_students')
                    ->label('لديها طلاب')
                    ->query(fn (Builder $query): Builder => $query->has('students')),
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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        $user = auth()->user();
        
        if ($user->hasRole('teacher')) {
            return $query->whereHas('teachers', function ($q) use ($user) {
                $q->where('teacher_id', $user->id);
            });
        }
        
        if ($user->hasRole('student')) {
            return $query->whereHas('students', function ($q) use ($user) {
                $q->where('student_id', $user->id);
            });
        }
        
        return $query;
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\StudentSubjectsRelationManager::class,
            RelationManagers\SubjectTeachersRelationManager::class,
            RelationManagers\HomeworksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubjects::route('/'),
            'create' => Pages\CreateSubject::route('/create'),
            'view' => Pages\ViewSubject::route('/{record}'),
            'edit' => Pages\EditSubject::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create subjects');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('edit subjects');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->can('delete subjects');
    }

    public static function canViewAny(): bool
    {
        return true; // جميع المستخدمين يمكنهم رؤية المواد
    }

}
