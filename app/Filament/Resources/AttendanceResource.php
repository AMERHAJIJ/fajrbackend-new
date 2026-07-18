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

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    public static function getNavigationLabel(): string { return __('admin.resources.attendance.plural_label'); }
    public static function getModelLabel(): string { return __('admin.resources.attendance.label'); }
    public static function getPluralModelLabel(): string { return __('admin.resources.attendance.plural_label'); }
    public static function getNavigationGroup(): ?string { return __('admin.navigation_group.education_management'); }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('admin.resources.attendance.label'))
                ->schema([
                    Forms\Components\Select::make('subject_id')
                        ->label(__('admin.fields.subject'))
                        ->relationship('subject', 'title')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->reactive()
                        ->disabled(fn ($context) => $context === 'edit')
                        ->options(function () {
                            if (Auth::check() && Auth::user()->roles->contains('name', 'teacher')) {
                                $userId = Auth::id();
                                return \App\Models\Subject::whereHas('teachers', function ($query) use ($userId) {
                                    $query->where('teacher_id', $userId);
                                })->pluck('title', 'id');
                            }
                            return \App\Models\Subject::pluck('title', 'id');
                        })
                        ->afterStateUpdated(function ($state, callable $set) {
                            if (!$state) {
                                $set('attendance_status', []);
                                return;
                            }
                            // Automatically fetch all students of this subject and set status to true (present)
                            $studentIds = \App\Models\User::role('student')
                                ->whereHas('subjectsAsStudent', function ($query) use ($state) {
                                    $query->where('subject_id', $state);
                                })
                                ->pluck('id')
                                ->toArray();

                            $attendanceStatus = [];
                            foreach ($studentIds as $id) {
                                $attendanceStatus[$id] = true;
                            }
                            $set('attendance_status', $attendanceStatus);
                        }),

                    // Student ID Select: Only visible in Edit mode
                    Forms\Components\Select::make('student_id')
                        ->label(__('admin.fields.student'))
                        ->relationship('student', 'name')
                        ->disabled()
                        ->visible(fn ($context) => $context === 'edit'),

                    // Single Status Toggle: Only visible in Edit mode
                    Forms\Components\Toggle::make('status')
                        ->label(__('admin.fields.status'))
                        ->onColor('success')
                        ->offColor('danger')
                        ->visible(fn ($context) => $context === 'edit'),

                    // Multiple Students Toggles: Only visible in Create mode
                    Forms\Components\Group::make()
                        ->label(__('admin.fields.status'))
                        ->visible(fn ($context, callable $get) => $context === 'create' && !empty($get('subject_id')))
                        ->schema(function (callable $get) {
                            $subjectId = $get('subject_id');
                            if (!$subjectId) {
                                return [];
                            }

                            // Fetch all students of this subject
                            $students = \App\Models\User::role('student')
                                ->whereHas('subjectsAsStudent', function ($query) use ($subjectId) {
                                    $query->where('subject_id', $subjectId);
                                })
                                ->orderBy('name')
                                ->get();

                            if ($students->isEmpty()) {
                                return [
                                    Forms\Components\Placeholder::make('no_students')
                                        ->label('')
                                        ->content('لا يوجد طلاب مسجلين في هذه الحلقة حالياً.'),
                                ];
                            }

                            return $students->map(function ($student) {
                                return Forms\Components\Toggle::make('attendance_status.' . $student->id)
                                    ->label($student->name)
                                    ->default(true)
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->inline(false)
                                    ->live();
                            })->toArray();
                        })
                        ->columns(3),

                    Forms\Components\DatePicker::make('date')
                        ->label(__('admin.fields.date'))
                        ->required()
                        ->default(now())
                        ->maxDate(now())
                        ->disabled(fn ($context) => $context === 'edit'),

                    Forms\Components\Hidden::make('attendance_status')
                        ->default([])
                ])->columns(2),
        ]);
    }

    public static function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $subjectId = $data['subject_id'];
        $date = $data['date'];
        $attendanceStatus = $data['attendance_status'] ?? [];

        // Fetch all students of this subject
        $studentIds = \App\Models\User::role('student')
            ->whereHas('subjectsAsStudent', function ($query) use ($subjectId) {
                $query->where('subject_id', $subjectId);
            })
            ->pluck('id')
            ->toArray();

        $attendances = [];

        foreach ($studentIds as $studentId) {
            // If the status is not present in the array, it means it is at its default (true / present)
            $status = (bool)($attendanceStatus[$studentId] ?? true);

            $existing = \App\Models\Attendance::where('student_id', $studentId)
                ->where('subject_id', $subjectId)
                ->whereDate('date', $date)
                ->first();

            if ($existing) {
                $existing->update(['status' => $status]);
                $attendances[] = $existing;
            } else {
                $attendances[] = \App\Models\Attendance::create([
                    'student_id' => $studentId,
                    'subject_id' => $subjectId,
                    'teacher_id' => Auth::id(),
                    'date' => $date,
                    'status' => $status,
                ]);
            }
        }

        return $attendances[0] ?? new \App\Models\Attendance($data);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label(__('admin.fields.student'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject.title')
                    ->label(__('admin.fields.subject'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label(__('admin.fields.date'))
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\IconColumn::make('status')
                    ->label(__('admin.fields.status'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('status')
                    ->label(__('admin.fields.status'))
                    ->boolean()
                    ->trueLabel(__('admin.options.present_only'))
                    ->falseLabel(__('admin.options.absent_only')),
                Tables\Filters\SelectFilter::make('subject_id')
                    ->label(__('admin.fields.subject'))
                    ->relationship('subject', 'title')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from_date')
                            ->label(__('admin.fields.from_date')),
                        Forms\Components\DatePicker::make('to_date')
                            ->label(__('admin.fields.to_date')),
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
                Tables\Actions\Action::make('send_whatsapp')
                    ->label('إرسال واتساب للغياب')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->visible(fn (Attendance $record) => !$record->status) // Only visible if student is absent
                    ->form(function (Attendance $record) {
                        $student = $record->student;
                        $parent = $student ? $student->parent : null;
                        
                        $fatherPhone = $student->father_phone ?? ($parent ? $parent->phone : null);
                        $motherPhone = $student->mother_phone ?? null;
                        
                        $options = [];
                        if ($fatherPhone) {
                            $options['father'] = "رقم هاتف الأب ({$fatherPhone}) - " . ($student->father_job ?? 'لا يوجد عمل');
                        }
                        if ($motherPhone) {
                            $options['mother'] = "رقم هاتف الأم ({$motherPhone}) - " . ($student->mother_job ?? 'لا يوجد عمل');
                        }
                        if (empty($options)) {
                            $options['default'] = "رقم هاتف الطالب الافتراضي (" . ($student->phone ?? 'لا يوجد') . ")";
                        }

                        // Default message
                        $studentName = $student ? $student->name : 'ابنكم';
                        $dateStr = \Carbon\Carbon::parse($record->date)->format('d/m/Y');
                        $subjectTitle = $record->subject ? $record->subject->title : '';
                        $defaultMessage = "السلام عليكم ورحمة الله وبركاته،\nنحيطكم علماً بغياب الطالب ({$studentName}) اليوم {$dateStr} عن حلقة ({$subjectTitle}). يرجى المتابعة وتأكيد سبب الغياب.";

                        return [
                            Forms\Components\Radio::make('recipient')
                                ->label('اختر المستلم')
                                ->options($options)
                                ->default(array_key_first($options))
                                ->required(),
                            Forms\Components\Textarea::make('message')
                                ->label('نص الرسالة')
                                ->rows(5)
                                ->default($defaultMessage)
                                ->required(),
                        ];
                    })
                    ->action(function (Attendance $record, array $data, \Livewire\Component $livewire) {
                        $student = $record->student;
                        $parent = $student ? $student->parent : null;
                        
                        $phone = null;
                        if ($data['recipient'] === 'father') {
                            $phone = $student->father_phone ?? ($parent ? $parent->phone : null);
                        } elseif ($data['recipient'] === 'mother') {
                            $phone = $student->mother_phone;
                        } else {
                            $phone = $student->phone;
                        }

                        if (!$phone) {
                            \Filament\Notifications\Notification::make()
                                ->title('خطأ')
                                ->body('لا يوجد رقم هاتف متاح للإرسال')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Clean phone number
                        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
                        if (str_starts_with($cleanPhone, '00')) {
                            $cleanPhone = substr($cleanPhone, 2);
                        } elseif (str_starts_with($cleanPhone, '0')) {
                            $cleanPhone = '90' . substr($cleanPhone, 1);
                        }
                        if (!str_starts_with($cleanPhone, '90') && strlen($cleanPhone) == 10 && str_starts_with($cleanPhone, '5')) {
                            $cleanPhone = '90' . $cleanPhone;
                        }

                        $whatsappUrl = "https://api.whatsapp.com/send?phone=" . urlencode($cleanPhone) . "&text=" . urlencode($data['message']);
                        
                        // Open in new tab
                        $livewire->js("window.open('{$whatsappUrl}', '_blank')");
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->groups([
                Tables\Grouping\Group::make('subject.title')
                    ->label('الحلقة')
                    ->collapsible(),
                Tables\Grouping\Group::make('date')
                    ->label('التاريخ')
                    ->date()
                    ->collapsible(),
            ])
            ->defaultGroup('subject.title')
            ->defaultSort('date', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        if (Auth::check()) {
            $user = Auth::user();
            $userId = Auth::id();
            
            if ($user->roles->contains('name', 'teacher')) {
                $query->whereHas('subject.teachers', function ($q) use ($userId) {
                    $q->where('teacher_id', $userId);
                });
            }

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