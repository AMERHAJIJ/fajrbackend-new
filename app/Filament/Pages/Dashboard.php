<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Models\Subject;
use App\Models\Attendance;
use App\Models\Quiz;
use App\Models\Video;
use App\Models\RecitationRecord;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\ChartWidget;

class Dashboard extends BaseDashboard
{
    
    protected static ?string $navigationIcon = 'heroicon-o-home';
    
    protected static string $view = 'filament.pages.dashboard';

    protected static ?string $title = 'لوحة التحكم';

    public function getWidgets(): array
    {
        return [
            DashboardStatsWidget::class,
            AttendanceChartWidget::class,
            RecitationScoresWidget::class,
        ];
    }
    
}

class DashboardStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalStudents = User::role('student')->count();
        $totalTeachers = User::role('teacher')->count();
        $totalSubjects = Subject::where('active', true)->count();
        $totalQuizzes = Quiz::where('active', true)->count();
        $totalVideos = Video::where('active', true)->count();
        
        // حساب نسبة الحضور العامة
        $totalAttendanceRecords = Attendance::count();
        $presentRecords = Attendance::where('status', true)->count();
        $attendanceRate = $totalAttendanceRecords > 0 
            ? round(($presentRecords / $totalAttendanceRecords) * 100, 1) 
            : 0;

        // متوسط درجات التلاوة
        $averageRecitationScore = RecitationRecord::avg('score');
        $averageRecitationScore = $averageRecitationScore ? round($averageRecitationScore, 1) : 0;

        return [
            BaseWidget\Stat::make('عدد الطلاب', $totalStudents)
                ->description('إجمالي الطلاب المسجلين')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),
            
            BaseWidget\Stat::make('عدد المعلمين', $totalTeachers)
                ->description('إجمالي المعلمين')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
            
            BaseWidget\Stat::make('Dersler', $totalSubjects)
                ->description('المواد النشطة')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('warning'),
            
            BaseWidget\Stat::make('نسبة الحضور', $attendanceRate . '%')
                ->description('نسبة الحضور العامة')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($attendanceRate >= 80 ? 'success' : ($attendanceRate >= 60 ? 'warning' : 'danger')),
            
            BaseWidget\Stat::make('Sınavlar', $totalQuizzes)
                ->description('الاختبارات النشطة')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),
            
            BaseWidget\Stat::make('Videolar', $totalVideos)
                ->description('الفيديوهات التعليمية')
                ->descriptionIcon('heroicon-m-video-camera')
                ->color('secondary'),
            
            BaseWidget\Stat::make('متوسط التلاوة', $averageRecitationScore . '%')
                ->description('متوسط درجات التلاوة')
                ->descriptionIcon('heroicon-m-microphone')
                ->color($averageRecitationScore >= 80 ? 'success' : ($averageRecitationScore >= 60 ? 'warning' : 'danger')),
        ];
    }
}

class AttendanceChartWidget extends ChartWidget
{
    protected static ?string $heading = 'إحصائيات الحضور الأسبوعية';

    protected function getData(): array
    {
        $data = [];
        $labels = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('d/m');
            
            $totalAttendance = Attendance::whereDate('date', $date)->count();
            $presentAttendance = Attendance::whereDate('date', $date)->where('status', true)->count();
            
            $attendanceRate = $totalAttendance > 0 ? ($presentAttendance / $totalAttendance) * 100 : 0;
            $data[] = round($attendanceRate, 1);
        }

        return [
            'datasets' => [
                [
                    'label' => 'نسبة الحضور %',
                    'data' => $data,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}

class RecitationScoresWidget extends ChartWidget
{
    protected static ?string $heading = 'توزيع درجات التلاوة';

    protected function getData(): array
    {
        $excellent = RecitationRecord::where('score', '>=', 90)->count();
        $veryGood = RecitationRecord::whereBetween('score', [80, 89])->count();
        $good = RecitationRecord::whereBetween('score', [70, 79])->count();
        $acceptable = RecitationRecord::whereBetween('score', [60, 69])->count();
        $weak = RecitationRecord::where('score', '<', 60)->count();

        return [
            'datasets' => [
                [
                    'data' => [$excellent, $veryGood, $good, $acceptable, $weak],
                    'backgroundColor' => [
                        'rgb(34, 197, 94)',
                        'rgb(59, 130, 246)', 
                        'rgb(245, 158, 11)',
                        'rgb(249, 115, 22)',
                        'rgb(239, 68, 68)',
                    ],
                ],
            ],
            'labels' => ['ممتاز (90+)', 'جيد جداً (80-89)', 'جيد (70-79)', 'مقبول (60-69)', 'ضعيف (<60)'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
