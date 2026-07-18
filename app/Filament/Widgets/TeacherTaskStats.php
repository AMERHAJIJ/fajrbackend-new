<?php

namespace App\Filament\Widgets;

use App\Models\TeacherTaskTracker;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TeacherTaskStats extends BaseWidget
{
    protected static ?int $sort = -2;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $user = Auth::user();

        // تظهر فقط للمسؤول (المدير)
        if (!$user->hasRole('admin')) {
            return [];
        }

        $today = Carbon::today();
        
        // جلب عدد المعلمين النشطين في النظام
        $teachersCount = User::role('teacher')->count();
        $totalPotentialTasks = $teachersCount * 5;

        if ($teachersCount === 0) {
            return [];
        }

        // جلب جميع سجلات التتبع لليوم لجميع المعلمين
        $trackers = TeacherTaskTracker::whereDate('date', $today)->get();

        $completedCount = 0;
        foreach ($trackers as $tracker) {
            $completedCount += ($tracker->attendance_taken ? 1 : 0) +
                               ($tracker->recitation_recorded ? 1 : 0) +
                               ($tracker->next_recitation_set ? 1 : 0) +
                               ($tracker->whatsapp_sent ? 1 : 0) +
                               ($tracker->homework_sent ? 1 : 0);
        }

        return [
            Stat::make('نسبة إنجاز مهام المعلمين لليوم', "{$completedCount} / {$totalPotentialTasks}")
                ->description($completedCount >= $totalPotentialTasks ? 'تم إكمال جميع مهام المعلمين اليومية' : 'إجمالي المهام اليومية المنجزة من كافة المعلمين')
                ->descriptionIcon($completedCount >= $totalPotentialTasks ? 'heroicon-m-check-badge' : 'heroicon-m-arrow-trending-up')
                ->color($completedCount >= ($totalPotentialTasks * 0.8) ? 'success' : ($completedCount >= ($totalPotentialTasks * 0.5) ? 'warning' : 'danger'))
                ->chart([$completedCount, $totalPotentialTasks]),
        ];
    }

    public static function canView(): bool
    {
        return Auth::check() && Auth::user()->hasRole('admin');
    }
}
