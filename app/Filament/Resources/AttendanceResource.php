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
use Filament\Forms\Components\Hidden;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Role;

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
        return $form->schema([
            Forms\Components\Section::make('معلومات الحضور')
                ->schema([
                    Forms\Components\Select::make('subject_id')
                        ->label('المادة')
                        ->relationship('subject', 'title')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->reactive()
                        ->options(function () {
                            if (Auth::check() && Auth::user()->roles->contains('name', 'teacher')) {
                                $userId = Auth::id();
                                return \App\Models\Subject::whereHas('teachers', function ($query) use ($userId) {
                                    $query->where('teacher_id', $userId);
                                })->pluck('title', 'id');
                            }
                            return \App\Models\Subject::pluck('title', 'id');
                        }),
                    Forms\Components\Select::make('selected_students')
                        ->label('اختر الطلاب')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->required()
                        ->options(function (callable $get) {
                            $subjectId = $get('subject_id');
                            if (!$subjectId) {
                                return [];
                            }
                            return \App\Models\User::role('student')
                                ->whereHas('subjectsAsStudent', function ($query) use ($subjectId) {
                                    $query->where('subject_id', $subjectId);
                                })
                                ->pluck('name', 'id');
                        })
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            // Initialize attendance status for selected students
                            $attendance = [];
                            foreach ($state as $studentId) {
                                $attendance[$studentId] = true; // Default to present
                            }
                            $set('attendance_status', $attendance);
                        }),
                    \Filament\Forms\Components\Group::make()
                        ->label('حالة الحضور للطلاب المحددين')
                        ->visible(fn (callable $get) => !empty($get('selected_students')))
                        ->schema(function (callable $get) {
                            $selectedStudents = $get('selected_students') ?? [];
                            $attendanceStatus = $get('attendance_status') ?? [];

                            if (empty($selectedStudents)) {
                                return [];
                            }

                            // Ensure all selected students have a status
                            foreach ($selectedStudents as $studentId) {
                                if (!array_key_exists($studentId, $attendanceStatus)) {
                                    $attendanceStatus[$studentId] = true; // Default to present
                                }
                            }

                            // Update the form state with any new defaults
                            if ($attendanceStatus !== $get('attendance_status')) {
                                // لا يمكن استخدام $set هنا، سيتم التعامل معه في afterStateUpdated
                            }

                            $studentRecords = \App\Models\User::whereIn('id', $selectedStudents)->get();

                            return $studentRecords->map(function ($student) use ($attendanceStatus) {
                                return \Filament\Forms\Components\Toggle::make('attendance_status.' . $student->id)
                                    ->label($student->name)
                                    ->default(true) // دائماً افتراضياً حاضر
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->inline(false)
                                    ->live(); // إضافة live للتحديث المباشر
                            })->toArray();
                        }),
                    Forms\Components\DatePicker::make('date')
                        ->label('تاريخ الحضور')
                        ->required()
                        ->default(now())
                        ->maxDate(now()),
                    Forms\Components\Hidden::make('attendance_status')
                        ->default(function ()   {
                            $status = [];
                            $students = request()->input('data.selected_students', []);
                            foreach ($students as $studentId) {
                                $status[$studentId] = true; // Default to present
                            }
                            return $status;
                        })
                ])->columns(2),
        ]);
    }

    public static function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $attendanceStatus = $data['attendance_status'] ?? [];
        $selectedStudents = $data['selected_students'] ?? [];

        // Make sure we have attendance status for all selected students
        foreach ($selectedStudents as $studentId) {
            if (!array_key_exists($studentId, $attendanceStatus)) {
                $attendanceStatus[$studentId] = true; // Default to present
            }
            // Ensure the status is a boolean
            $attendanceStatus[$studentId] = (bool)($attendanceStatus[$studentId] ?? true);
        }

        $subjectId = $data['subject_id'];
        $date = $data['date'];

        // Remove unneeded fields
        unset($data['attendance_status'], $data['selected_students']);

        $attendances = [];

        // Process each student's attendance
        foreach ($attendanceStatus as $studentId => $status) {
            if (!in_array($studentId, $selectedStudents)) {
                continue; // Skip if student is not in the selected students
            }

            // Check if attendance already exists for this student, subject, and date
            $existing = \App\Models\Attendance::where('student_id', $studentId)
                ->where('subject_id', $subjectId)
                ->whereDate('date', $date)
                ->first();

            if ($existing) {
                // Update existing record
                $existing->update(['status' => $status]);
                $attendances[] = $existing;
            } else {
                // Create new record
                $attendances[] = new \App\Models\Attendance([
                    'student_id' => $studentId,
                    'subject_id' => $subjectId,
                    'date' => $date,
                    'status' => $status,
                ]);
            }
        }

        // Save all attendance records
        foreach ($attendances as $attendance) {
            if (!$attendance->exists) {
                $attendance->save();
            }
        }

        // Return the first attendance record (for Filament)
        return $attendances[0] ?? new \App\Models\Attendance($data);
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
        
        if (Auth::check()) {
            $user = Auth::user();
            $userId = Auth::id();
            
            // For teachers, only show attendance for their subjects
            if ($user->roles->contains('name', 'teacher')) {
                $query->whereHas('subject.teachers', function ($q) use ($userId) {
                    $q->where('teacher_id', $userId);
                });
            }

            // For students, only show their own attendance
            if ($user->roles->contains('name', 'student')) {
                $query->where('student_id', $userId);
            }
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
        return Auth::check() && Auth::user()->roles->whereIn('name', ['admin', 'teacher'])->isNotEmpty();
    }

    public static function canCreate(): bool
    {
        return Auth::check() && Auth::user()->roles->whereIn('name', ['admin', 'teacher'])->isNotEmpty();
    }

    public static function canEdit($record): bool
    {
        return Auth::check() && Auth::user()->roles->whereIn('name', ['admin', 'teacher'])->isNotEmpty();
    }

    public static function canDelete($record): bool
    {
        return Auth::check() && Auth::user()->roles->contains('name', 'admin');
    }
}