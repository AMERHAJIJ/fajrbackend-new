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
    public static function getNavigationLabel(): string { return __('admin.pages.student_statistics.title'); }
    public function getTitle(): string | \Illuminate\Contracts\Support\Htmlable { return __('admin.pages.student_statistics.title'); }
    public static function getNavigationGroup(): ?string { return __('admin.navigation_group.reports_statistics'); }
    protected static ?int $navigationSort = 1;

    // إخفاء الصفحة من غير المديرين
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    // التحقق من الصلاحيات للوصول للصفحة
    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    protected static string $view = 'filament.pages.student-statistics';

    public string $reportType = 'daily';
    public ?int $selectedSubject = null;
    public ?string $selectedDate = null;

    public function mount(): void
    {
        if (!auth()->user()?->hasRole('admin')) {
            abort(403, __('admin.messages.no_permission'));
        }

        $this->selectedDate = now()->format('Y-m-d');
        $this->selectedSubject = null;
        $this->reportType = 'daily';
    }

    public function getTableQuery(): Builder
    {
        if (!$this->selectedSubject) {
            return User::role('student')->whereRaw('1 = 0');
        }

        $query = User::role('student')
            ->whereHas('subjectsAsStudent', function($q) {
                $q->where('subject_id', $this->selectedSubject);
            });

        if ($this->reportType === 'daily') {
            if (!$this->selectedDate) {
                return User::role('student')->whereRaw('1 = 0');
            }
            $query->with([
                'attendances' => function($q) {
                    $q->where('subject_id', $this->selectedSubject)
                      ->whereDate('date', $this->selectedDate);
                },
                'recitationRecords' => function($q) {
                    $q->where('subject_id', $this->selectedSubject)
                      ->whereDate('date', $this->selectedDate)
                      ->with('surahs');
                }
            ]);
        } else {
            // Cumulative
            $query->with([
                'attendances' => function($q) {
                    $q->where('subject_id', $this->selectedSubject);
                },
                'recitationRecords' => function($q) {
                    $q->where('subject_id', $this->selectedSubject)
                      ->with(['surahs', 'teacher']);
                }
            ]);
        }

        return $query;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('تصفية البيانات للتقارير')
                    ->schema([
                        Select::make('reportType')
                            ->label('نوع التقرير')
                            ->options([
                                'daily' => 'تقرير يومي محدد',
                                'cumulative' => 'تقرير تراكمي عام',
                            ])
                            ->default('daily')
                            ->live()
                            ->required()
                            ->afterStateUpdated(function ($state) {
                                $this->reportType = $state;
                                $this->resetTable();
                                $this->dispatch('$refresh');
                            }),

                        Select::make('selectedSubject')
                            ->label('الحلقة')
                            ->options(Subject::where('active', true)->pluck('title', 'id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required()
                            ->afterStateUpdated(function ($state) {
                                $this->selectedSubject = $state;
                                $this->resetTable();
                                $this->dispatch('$refresh');
                            }),
                        
                        DatePicker::make('selectedDate')
                            ->label('التاريخ')
                            ->default(now())
                            ->live()
                            ->visible(fn (callable $get) => $get('reportType') === 'daily')
                            ->afterStateUpdated(function ($state) {
                                $this->selectedDate = $state;
                                $this->resetTable();
                                $this->dispatch('$refresh');
                            })
                    ])
                    ->columns(3)
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
                
                // Cumulative columns
                TextColumn::make('age')
                    ->label('العمر')
                    ->visible(fn () => $this->reportType === 'cumulative')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime('d/m/Y')
                    ->visible(fn () => $this->reportType === 'cumulative')
                    ->sortable(),

                // Daily columns
                TextColumn::make('attendance_status')
                    ->label('حالة الحضور')
                    ->visible(fn () => $this->reportType === 'daily')
                    ->getStateUsing(function ($record) {
                        if (!$this->selectedDate) return 'لم يسجل';
                        $attendance = $record->attendances->first();
                        if (!$attendance) return 'لم يسجل';
                        return $attendance->status ? 'حاضر' : 'غائب';
                    })
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'حاضر' => 'success',
                        'غائب' => 'danger',
                        default => 'gray',
                    }),
                
                TextColumn::make('recitation_info')
                    ->label('التسميع اليومي')
                    ->visible(fn () => $this->reportType === 'daily')
                    ->getStateUsing(function ($record) {
                        if (!$this->selectedDate) return 'لا يوجد';
                        $recitation = $record->recitationRecords->first();
                        if (!$recitation) return 'لا يوجد';
                        $surahs = $recitation->surahs->map(function($surah) {
                            if ($surah->pivot->type === 'ayah') {
                                return "{$surah->name} (من آية {$surah->pivot->fromAyeh} إلى {$surah->pivot->toAyeh})";
                            } else {
                                return "{$surah->name} (من صفحة {$surah->pivot->fromPage} إلى {$surah->pivot->toPage})";
                            }
                        })->implode('، ');
                        return $surahs ?: 'لا يوجد';
                    })
                    ->wrap(),
                
                TextColumn::make('recitation_score')
                    ->label('تقييم التسميع')
                    ->visible(fn () => $this->reportType === 'daily')
                    ->getStateUsing(function ($record) {
                        if (!$this->selectedDate) return 'لا يوجد';
                        $recitation = $record->recitationRecords->first();
                        if (!$recitation) return 'لا يوجد';
                        return $recitation->score . '%';
                    })
                    ->badge()
                    ->color(function (string $state): string {
                        if ($state === 'لا يوجد') return 'gray';
                        $score = (int) $state;
                        if ($score >= 90) return 'success';
                        if ($score >= 80) return 'info';
                        if ($score >= 60) return 'warning';
                        return 'danger';
                    }),
                
                // Cumulative summary columns
                TextColumn::make('attendance_summary')
                    ->label('الحضور والغياب الكلي')
                    ->visible(fn () => $this->reportType === 'cumulative')
                    ->getStateUsing(function ($record) {
                        $total = $record->attendances->count();
                        if ($total === 0) return 'لم يسجل حضور';
                        $present = $record->attendances->where('status', true)->count();
                        $percentage = round(($present / $total) * 100);
                        return "{$present} / {$total} يوماً ({$percentage}%)";
                    })
                    ->badge()
                    ->color('info'),

                TextColumn::make('total_pages_memorized')
                    ->label('إجمالي الصفحات المحفوظة')
                    ->visible(fn () => $this->reportType === 'cumulative')
                    ->getStateUsing(function ($record) {
                        $totalPages = 0;
                        foreach ($record->recitationRecords as $recordItem) {
                            foreach ($recordItem->surahs as $surah) {
                                if ($surah->pivot->type === 'page') {
                                    $totalPages += max(0, $surah->pivot->toPage - $surah->pivot->fromPage + 1);
                                } else {
                                    if ($surah->pivot->fromPage && $surah->pivot->toPage) {
                                        $totalPages += max(0, $surah->pivot->toPage - $surah->pivot->fromPage + 1);
                                    }
                                }
                            }
                        }
                        return $totalPages . ' صفحة';
                    }),

                TextColumn::make('average_score')
                    ->label('معدل التسميع الكلي')
                    ->visible(fn () => $this->reportType === 'cumulative')
                    ->getStateUsing(function ($record) {
                        $totalRecords = $record->recitationRecords->count();
                        if ($totalRecords === 0) return 'لا يوجد';
                        $avg = $record->recitationRecords->avg('score');
                        return number_format($avg, 1) . '%';
                    })
                    ->badge()
                    ->color(function (string $state): string {
                        if ($state === 'لا يوجد') return 'gray';
                        $score = (float) $state;
                        if ($score >= 90) return 'success';
                        if ($score >= 80) return 'info';
                        if ($score >= 60) return 'warning';
                        return 'danger';
                    }),

                TextColumn::make('completed_surahs')
                    ->label('المحصلات (السور المسجلة)')
                    ->visible(fn () => $this->reportType === 'cumulative')
                    ->getStateUsing(function ($record) {
                        $surahNames = [];
                        foreach ($record->recitationRecords as $recordItem) {
                            foreach ($recordItem->surahs as $surah) {
                                $surahNames[] = $surah->name;
                            }
                        }
                        $uniqueSurahs = array_unique($surahNames);
                        return empty($uniqueSurahs) ? 'لا يوجد' : implode('، ', $uniqueSurahs);
                    })
                    ->wrap(),

                TextColumn::make('primary_teacher')
                    ->label('الأستاذ المتابع')
                    ->visible(fn () => $this->reportType === 'cumulative')
                    ->getStateUsing(function ($record) {
                        $teachers = [];
                        foreach ($record->recitationRecords as $recordItem) {
                            if ($recordItem->teacher) {
                                $teachers[] = $recordItem->teacher->name;
                            }
                        }
                        if (empty($teachers)) {
                            $subject = Subject::find($this->selectedSubject);
                            return $subject?->teachers?->first()?->name ?? 'غير محدد';
                        }
                        $counts = array_count_values($teachers);
                        arsort($counts);
                        return array_key_first($counts);
                    }),
                
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->visible(fn () => $this->reportType === 'daily')
                    ->getStateUsing(fn () => $this->selectedDate ?? 'غير محدد')
                    ->date('d/m/Y'),
            ])
            ->filters([
                SelectFilter::make('attendance_status')
                    ->label('حالة الحضور اليومي')
                    ->visible(fn () => $this->reportType === 'daily')
                    ->options([
                        'present' => 'حاضر',
                        'absent' => 'غائب',
                        'not_registered' => 'لم يسجل حضور',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!$data['value']) {
                            return $query;
                        }
                        
                        if (!$this->selectedDate) return $query;
                        
                        return $query->whereHas('attendances', function ($q) use ($data) {
                            $q->whereDate('date', $this->selectedDate);
                            
                            if ($data['value'] === 'present') {
                                $q->where('status', true);
                            } elseif ($data['value'] === 'absent') {
                                $q->where('status', false);
                            } elseif ($data['value'] === 'not_registered') {
                                $q->whereRaw('1 = 0');
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
            Action::make('update_google_sheets')
                ->label('تحديث جداول بيانات Google')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('تصدير البيانات إلى Google Sheets')
                ->modalDescription('هل أنت متأكد من رغبتك في تصدير وتحديث البيانات في Google Sheets؟')
                ->action(function () {
                    if (!$this->selectedSubject) {
                        Notification::make()
                            ->title('خطأ')
                            ->body('يجب اختيار حلقة أولاً.')
                            ->danger()
                            ->send();
                        return;
                    }

                    if ($this->reportType === 'daily' && !$this->selectedDate) {
                        Notification::make()
                            ->title('خطأ في التصدير')
                            ->body('يجب تحديد التاريخ أولاً للتقرير اليومي.')
                            ->danger()
                            ->send();
                        return;
                    }

                    try {
                        $googleSheetsService = app(GoogleSheetsService::class);
                        
                        if (!$googleSheetsService->testConnection()) {
                            Notification::make()
                                ->title('فشل الاتصال')
                                ->body('لا يمكن الاتصال بـ Google Sheets. يرجى التحقق من الملف الأمني وإعدادات الاتصال.')
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        $exportDate = $this->reportType === 'daily' ? $this->selectedDate : now()->format('Y-m-d');
                        $result = $googleSheetsService->updateStudentReport($this->selectedSubject, $exportDate, $this->reportType);
                        
                        if ($result) {
                            Notification::make()
                                ->title('تم التصدير بنجاح')
                                ->body('تم تحديث البيانات في Google Sheets بنجاح وفقاً لنوع التقرير المحدد.')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('فشل التصدير')
                                ->body('فشل تحديث البيانات في Google Sheets.')
                                ->danger()
                                ->send();
                        }
                    } catch (\Exception $e) {
                        $errorMessage = $e->getMessage();
                        
                        if (strpos($errorMessage, '404') !== false || strpos($errorMessage, 'not found') !== false) {
                            $errorMessage = 'ملف جداول بيانات جوجل غير موجود أو لا يمكن الوصول إليه.';
                        } elseif (strpos($errorMessage, '403') !== false || strpos($errorMessage, 'permission') !== false) {
                            $errorMessage = 'صلاحيات حساب الخدمة غير كافية للوصول للملف.';
                        }
                        
                        Notification::make()
                            ->title('حدث خطأ أثناء التصدير')
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