<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use App\Models\Subject;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'الحضور';

    protected static ?string $modelLabel = 'حضور';

    protected static ?string $pluralModelLabel = 'الحضور';

    protected static ?string $navigationGroup = 'إدارة التعليم';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الحضور')
                    ->schema([
                        Forms\Components\CheckboxList::make('present_students')
                            ->label('الطلاب الحاضرين')
                            ->options(function (callable $get) {
                                $subjectId = $get('subject_id');
                                if (!$subjectId) {
                                    return [];
                                }
                                
                                if (auth()->user()->hasRole('teacher')) {
                                    // المعلم يرى طلاب المادة التي يدرسها فقط
                                    return User::role('student')
                                        ->whereHas('subjectsAsStudent', function ($query) use ($subjectId) {
                                            $query->where('subject_id', $subjectId);
                                        })
                                        ->pluck('name', 'id');
                                }
                                return User::role('student')
                                    ->whereHas('subjectsAsStudent', function ($query) use ($subjectId) {
                                        $query->where('subject_id', $subjectId);
                                    })
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(3)
                            ->helperText('ضع علامة على الطلاب الحاضرين فقط')
                            ->dehydrated(false), // لا يتم حفظه في قاعدة البيانات
                        Forms\Components\Select::make('subject_id')
                            ->label('المادة')
                            ->relationship('subject', 'title')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->options(function () {
                                if (auth()->user()->hasRole('teacher')) {
                                    // المعلم يرى المواد التي يدرسها فقط
                                    return Subject::whereHas('teachers', function ($query) {
                                        $query->where('teacher_id', auth()->id());
                                    })->pluck('title', 'id');
                                }
                                return Subject::pluck('title', 'id');
                            }),
                        Forms\Components\DatePicker::make('date')
                            ->label('تاريخ الحضور')
                            ->required()
                            ->default(now())
                            ->maxDate(now()),
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
                Tables\Columns\TextColumn::make('subject.title')
                    ->label('المادة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\IconColumn::make('status')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('status')
                    ->label('حالة الحضور')
                    ->boolean()
                    ->trueLabel('حاضر فقط')
                    ->falseLabel('غائب فقط'),
                Tables\Filters\SelectFilter::make('subject_id')
                    ->label('المادة')
                    ->relationship('subject', 'title')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from_date')
                            ->label('من تاريخ'),
                        Forms\Components\DatePicker::make('to_date')
                            ->label('إلى تاريخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['to_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
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
            ])
            ->defaultSort('date', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // إذا كان المستخدم معلم، عرض حضور طلاب المواد التي يدرسها فقط
        if (auth()->user()->hasRole('teacher')) {
            $query->whereHas('subject.teachers', function ($q) {
                $q->where('teacher_id', auth()->id());
            });
        }
        
        // إذا كان المستخدم طالب، عرض حضوره فقط
        if (auth()->user()->hasRole('student')) {
            $query->where('student_id', auth()->id());
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
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'view' => Pages\ViewAttendance::route('/{record}'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'teacher']);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasRole(['admin', 'teacher']);
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->hasRole(['admin', 'teacher']);
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->hasRole('admin');
    }
}
