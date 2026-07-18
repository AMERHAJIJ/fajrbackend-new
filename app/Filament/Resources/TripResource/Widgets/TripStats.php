<?php

namespace App\Filament\Resources\TripResource\Widgets;

use App\Models\Trip;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class TripStats extends BaseWidget
{
    public ?Model $record = null;

    protected function getStats(): array
    {
        if (!$this->record) {
            return [];
        }

        $confirmedParticipants = $this->record->participants()->where('status', 'confirmed')->count();
        $totalCollected = $this->record->participants()->sum('paid_amount');
        $expectedRevenue = $confirmedParticipants * $this->record->cost_per_student;
        $remainingBalance = max(0, $expectedRevenue - $totalCollected);
        
        $totalBusCapacity = $this->record->buses()->sum('capacity');
        $busOccupancy = $totalBusCapacity > 0 ? round(($confirmedParticipants / $totalBusCapacity) * 100, 1) : 0;

        return [
            Stat::make('المشاركون المؤكدون', $confirmedParticipants)
                ->description('من أصل ' . ($this->record->capacity ?? 'غير محدد') . ' مقعد')
                ->icon('heroicon-o-users'),
            
            Stat::make('إجمالي التحصيل المالي', '$' . number_format($totalCollected, 2))
                ->description('المتبقي: $' . number_format($remainingBalance, 2))
                ->icon('heroicon-o-banknotes')
                ->color('success'),
            
            Stat::make('نسبة إشغال الباصات', $busOccupancy . '%')
                ->description('سعة الباصات الإجمالية: ' . $totalBusCapacity)
                ->icon('heroicon-o-truck')
                ->color($busOccupancy > 90 ? 'danger' : 'success'),
                
            Stat::make('تقدير كميات الطعام', $confirmedParticipants)
                ->description('وجبة بناءً على المشاركين المؤكدين')
                ->icon('heroicon-o-shopping-cart'),
        ];
    }
}
