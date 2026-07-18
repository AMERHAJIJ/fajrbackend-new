<?php

namespace App\Filament\Widgets;

use App\Models\VideoAnalytic;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AiAnomalyWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $user = auth()->user();
        
        $query = VideoAnalytic::query();
        
        if ($user->hasRole('teacher')) {
            $query->whereHas('video', function($q) use ($user) {
                $q->whereHas('subject', function($sq) use ($user) {
                    $sq->whereHas('teachers', function($tq) use ($user) {
                        $tq->where('users.id', $user->id);
                    });
                });
            });
        }

        $totalChecked = (clone $query)->count();
        $anomaliesCount = (clone $query)->where('is_anomaly', true)->count();
        $avgScore = (clone $query)->avg('anomaly_score') ?? 0;

        return [
            Stat::make(__('admin.resources.video_analytic.plural_label'), $totalChecked)
                ->description(__('admin.messages.total_scanned'))
                ->icon('heroicon-o-eye'),
            Stat::make(__('admin.fields.is_anomaly'), $anomaliesCount)
                ->description(__('admin.messages.anomalies_detected'))
                ->color($anomaliesCount > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-exclamation-triangle'),
            Stat::make(__('admin.fields.anomaly_score'), number_format($avgScore, 2))
                ->description(__('admin.messages.avg_risk_score'))
                ->icon('heroicon-o-chart-bar'),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->hasRole('admin') || auth()->user()->can('view_smart_innovation');
    }
}
