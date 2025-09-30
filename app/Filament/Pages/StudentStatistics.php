<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Models\Subject;
use App\Services\GoogleSheetsService;
use Filament\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class StudentStatistics extends Page implements HasForms, HasActions, HasTable
{
    use InteractsWithForms, InteractsWithActions, InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'إحصائيات الطلاب';
    protected static ?string $title = 'إحصائيات الطلاب';
    protected static ?string $navigationGroup = 'التقارير والإحصائيات';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.student-statistics';

    public ?int $selectedSubject = null;
    public ?string $selectedDate = null;

    public function mount(): void
    {
        $this->selectedDate = now()->format('Y-m-d');
        $this->selectedSubject = null;
    }

    public function getTableQuery(): Builder
    {
        // إذا لم يتم اختيار مادة أو تاريخ، إرجاع query فارغ
        if (!$this->selectedSubject || !$this->selectedDate) {
            return User::role('student')->whereRaw('1 = 0');
        }

        $query = User::role('student')
            ->whereHas('subjectsAsStudent', function($q) {
                $q->where('subject_id', $this->selectedSubject);
            })
            ->with([
                'attendances' => function($query) {
                    $query->whereDate('date', $this->selectedDate);
                },
                'recitationRecords' => function($query) {
                    $query->whereDate('date', $this->selectedDate)
                        ->with('surahs');
                }
            ]);

        return $query;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('فلترة البيانات')
                    ->schema([
                        Select::make('selectedSubject')
                            ->label('المادة')
                            ->options(Subject::where('active', true)->pluck('title', 'id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required()
                            ->afterStateUpdated(function ($state) {
                                $this->selectedSubject = $state;
                                $this->resetTable();
                                $this->dispatch('$refresh');
                            })
                            ->helperText('يجب اختيار مادة لعرض الطلاب'),
                        
                        DatePicker::make('selectedDate')
                            ->label('التاريخ')
                            ->default(now())
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->selectedDate = $state;
                                $this->resetTable();
                                $this->dispatch('$refresh');
                            })
                            ->helperText('يمكنك اختيار أي تاريخ للبحث عن البيانات')
                            ->required(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('name')
                    ->label('اسم الطالب')
                    ->searchable()
                    ->sortable(),
                
                       TextColumn::make('attendance_status')
                           ->label('الحضور')
                           ->getStateUsing(function ($record) {
                               if (!$this->selectedDate) return 'لم يتم التسجيل';
                               
                               // استخدام البيانات المحملة مسبقاً بدلاً من query جديد
                               $attendance = $record->attendances->first();
                               
                               if (!$attendance) {
                                   return 'لم يتم التسجيل';
                               }
                               
                               return $attendance->status ? 'حاضر' : 'غائب';
                           })
                    ->badge()
                    ->color(function (string $state): string {
                        if ($state === 'حاضر') return 'success';
                        if ($state === 'غائب') return 'danger';
                        return 'gray';
                    }),
                
                       TextColumn::make('recitation_info')
                           ->label('ما سمع')
                           ->getStateUsing(function ($record) {
                               if (!$this->selectedDate) return 'لا يوجد';
                               
                               // استخدام البيانات المحملة مسبقاً بدلاً من query جديد
                               $recitation = $record->recitationRecords->first();
                               
                               if (!$recitation) {
                                   return 'لا يوجد';
                               }
                               
                               $surahs = $recitation->surahs->map(function($surah) {
                                   return "{$surah->name} (من آية {$surah->pivot->fromAyeh} إلى {$surah->pivot->toAyeh})";
                               })->implode('، ');
                               
                               return $surahs ?: 'لا يوجد';
                           })
                    ->wrap(),
                
                       TextColumn::make('recitation_score')
                           ->label('النتيجة')
                           ->getStateUsing(function ($record) {
                               if (!$this->selectedDate) return 'لا يوجد';
                               
                               // استخدام البيانات المحملة مسبقاً بدلاً من query جديد
                               $recitation = $record->recitationRecords->first();
                               
                               if (!$recitation) {
                                   return 'لا يوجد';
                               }
                               
                               return $recitation->score . '/10';
                           })
                    ->badge()
                    ->color(function (string $state): string {
                        if ($state === 'لا يوجد') return 'gray';
                        
                        $score = (int) explode('/', $state)[0];
                        if ($score >= 90) return 'success';
                        if ($score >= 80) return 'info';
                        if ($score >= 70) return 'warning';
                        if ($score >= 60) return 'danger';
                        return 'gray';
                    }),
                
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->getStateUsing(fn () => $this->selectedDate ?? 'غير محدد')
                    ->date('Y-m-d'),
            ])
            ->filters([
                SelectFilter::make('attendance_status')
                    ->label('حالة الحضور')
                    ->options([
                        'present' => 'حاضر',
                        'absent' => 'غائب',
                        'not_registered' => 'لم يتم التسجيل',
                    ])
                           ->query(function (Builder $query, array $data): Builder {
                               if (!$data['value']) {
                                   return $query;
                               }
                               
                               if (!$this->selectedDate) return $query;
                               
                               // تحسين الفلتر ليكون أكثر كفاءة
                               return $query->whereHas('attendances', function ($q) use ($data) {
                                   $q->whereDate('date', $this->selectedDate);
                                   
                                   if ($data['value'] === 'present') {
                                       $q->where('status', true);
                                   } elseif ($data['value'] === 'absent') {
                                       $q->where('status', false);
                                   } elseif ($data['value'] === 'not_registered') {
                                       // للطلاب الذين لم يتم تسجيل حضورهم
                                       $q->whereRaw('1 = 0'); // لا يوجد سجلات حضور
                                   }
                               });
                           }),
            ])
            ->defaultSort('name')
            ->paginated([10, 25, 50, 100])
            ->poll('30s');
    }


    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_to_google_sheets')
                ->label('تصدير إلى Google Sheets')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('تصدير البيانات إلى Google Sheets')
                ->modalDescription('سيتم تصدير بيانات الطلاب المحددة إلى Google Sheets. هل تريد المتابعة؟')
                ->action(function () {
                    // التحقق من وجود المادة المحددة
                    if (!$this->selectedSubject) {
                        Notification::make()
                            ->title('خطأ')
                            ->body('يجب اختيار مادة أولاً')
                            ->danger()
                            ->send();
                        return;
                    }

                    // التحقق من وجود التاريخ
                    if (!$this->selectedDate) {
                        Notification::make()
                            ->title('خطأ')
                            ->body('يجب اختيار تاريخ أولاً')
                            ->danger()
                            ->send();
                        return;
                    }

                    try {
                        $googleSheetsService = app(GoogleSheetsService::class);
                        
                        // Test connection first
                        if (!$googleSheetsService->testConnection()) {
                            Notification::make()
                                ->title('خطأ في الاتصال')
                                ->body('لا يمكن الاتصال بـ Google Sheets. تحقق من الإعدادات.')
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        $result = $googleSheetsService->updateStudentReport($this->selectedSubject, $this->selectedDate);
                        
                        if ($result) {
                            Notification::make()
                                ->title('تم التصدير بنجاح')
                                ->body('تم تصدير بيانات الطلاب إلى Google Sheets بنجاح')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('فشل التصدير')
                                ->body('حدث خطأ أثناء تصدير البيانات')
                                ->danger()
                                ->send();
                        }
                    } catch (\Exception $e) {
                        $errorMessage = $e->getMessage();
                        
                        // Provide more specific error messages
                        if (strpos($errorMessage, '404') !== false || strpos($errorMessage, 'not found') !== false) {
                            $errorMessage = 'الجدول غير موجود. تحقق من Spreadsheet ID.';
                        } elseif (strpos($errorMessage, '403') !== false || strpos($errorMessage, 'permission') !== false) {
                            $errorMessage = 'لا توجد صلاحيات للوصول للجدول. تحقق من إعدادات Service Account.';
                        } elseif (strpos($errorMessage, 'Service Account') !== false) {
                            $errorMessage = 'مشكلة في ملف Service Account. تحقق من الملف وصلاحياته.';
                        }
                        
                        Notification::make()
                            ->title('حدث خطأ')
                            ->body('فشل تصدير البيانات: ' . $errorMessage)
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    public function getTableRecords(): Collection
    {
        return $this->getTableQuery()->get();
    }
}