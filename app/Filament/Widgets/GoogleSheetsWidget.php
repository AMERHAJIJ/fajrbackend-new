<?php

namespace App\Filament\Widgets;

use App\Services\GoogleSheetsService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class GoogleSheetsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '300s';
    protected  ?string $heading = 'إحصائيات النظام';
    protected static ?int $sort = 1;
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        $stats = Cache::remember('google_sheets_stats', now()->addHours(1), function () {
            $service = app(GoogleSheetsService::class);
            $data = $service->collectStatistics();
            
            return [
                'total_users' => [
                    'value' => $data['total_users'] ?? 0,
                    'description' => 'إجمالي المستخدمين',
                    'icon' => 'heroicon-o-users',
                    'color' => 'primary',
                ],
                'active_users' => [
                    'value' => $data['active_users'] ?? 0,
                    'description' => 'المستخدمون النشطون (آخر 30 يوم)',
                    'icon' => 'heroicon-o-user-group',
                    'color' => 'success',
                ],
                'total_videos' => [
                    'value' => $data['total_videos'] ?? 0,
                    'description' => 'إجمالي الفيديوهات',
                    'icon' => 'heroicon-o-video-camera',
                    'color' => 'danger',
                ],
                'total_quizzes' => [
                    'value' => $data['total_quizzes'] ?? 0,
                    'description' => 'إجمالي الاختبارات',
                    'icon' => 'heroicon-o-document-text',
                    'color' => 'warning',
                ],
                'total_attendance' => [
                    'value' => $data['total_attendance'] ?? 0,
                    'description' => 'سجلات الحضور والغياب',
                    'icon' => 'heroicon-o-clipboard-list',
                    'color' => 'info',
                ],
                'total_recitations' => [
                    'value' => $data['total_recitations'] ?? 0,
                    'description' => 'سجلات التسميع',
                    'icon' => 'heroicon-o-musical-note',
                    'color' => 'success',
                ],
            ];
        });

        return collect($stats)->map(function ($stat, $key) {
            return Stat::make(
                number_format($stat['value']),
                $stat['description']
            )
            ->descriptionIcon($stat['icon'])
            ->color($stat['color']);
        })->toArray();
    }

    public static function canView(): bool
    {
        return auth()->user()->hasRole('admin');
    }
}
