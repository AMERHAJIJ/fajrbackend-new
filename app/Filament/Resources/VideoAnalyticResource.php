<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VideoAnalyticResource\Pages;
use App\Models\VideoAnalytic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class VideoAnalyticResource extends Resource
{
    protected static ?string $model = VideoAnalytic::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('admin') || auth()->user()->can('view_smart_innovation');
    }

    public static function getNavigationLabel(): string { return __('admin.resources.video_analytic.plural_label'); }
    public static function getModelLabel(): string { return __('admin.resources.video_analytic.label'); }
    public static function getPluralModelLabel(): string { return __('admin.resources.video_analytic.plural_label'); }
    public static function getNavigationGroup(): ?string { return __('admin.navigation_group.education_management'); }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('admin.resources.video_analytic.label'))
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->relationship('student', 'name')
                            ->label(__('admin.fields.student'))
                            ->disabled(),
                        Forms\Components\Select::make('video_id')
                            ->relationship('video', 'name')
                            ->label(__('admin.fields.video'))
                            ->disabled(),
                        Forms\Components\TextInput::make('watched_duration')
                            ->label(__('admin.fields.watched_duration'))
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('pause_count')
                            ->label(__('admin.fields.pause_count'))
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('app_switch_count')
                            ->label(__('admin.fields.app_switch_count'))
                            ->numeric()
                            ->disabled(),
                        Forms\Components\Toggle::make('is_anomaly')
                            ->label(__('admin.fields.is_anomaly'))
                            ->disabled(),
                        Forms\Components\TextInput::make('anomaly_score')
                            ->label(__('admin.fields.anomaly_score'))
                            ->numeric()
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label(__('admin.fields.student'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('video.name')
                    ->label(__('admin.fields.video'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_anomaly')
                    ->label(__('admin.fields.is_anomaly'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('anomaly_score')
                    ->label(__('admin.fields.anomaly_score'))
                    ->numeric(2)
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.fields.date'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_anomaly')
                    ->label(__('admin.fields.is_anomaly')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('runAiAnalysis')
                    ->label(__('admin.actions.run_ai_analysis'))
                    ->icon('heroicon-o-cpu-chip')
                    ->action(fn (Collection $records) => static::runAnalysis($records))
                    ->deselectRecordsAfterCompletion(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('analyzeAll')
                    ->label(__('admin.actions.analyze_all'))
                    ->icon('heroicon-o-bolt')
                    ->action(fn () => static::runAnalysis(VideoAnalytic::all()))
                    ->color('warning'),
            ]);
    }

    // [شرح أكاديمي للمناقشة]:
    // هذه الدالة هي نقطة الربط (Integration) بين الـ Backend والذكاء الاصطناعي.
    // السيرفر هنا يجمع بيانات المشاهدة من قاعدة البيانات ويحولها لصيغة JSON.
    // ثم يقوم بتشغيل ملف بايثون (Isolation Forest) في الخلفية ويرسل له البيانات.
    // بايثون يُحلل السلوك، ويعيد النتيجة (شاذ أو طبيعي + السكور) للـ Laravel، 
    // ليتم عرضها في هذا الجدول كتحذيرات للإدارة.
    public static function runAnalysis(Collection $records)
    {
        if ($records->isEmpty()) {
            Notification::make()->title('لا توجد بيانات للتحليل')->warning()->send();
            return;
        }

        $data = $records->map(fn ($r) => [
            'id' => $r->id,
            'watched_duration' => $r->watched_duration,
            'pause_count' => $r->pause_count,
            'forward_skip_count' => $r->forward_skip_count,
            'backward_skip_count' => $r->backward_skip_count,
            'playback_rate' => $r->playback_rate,
            'app_switch_count' => $r->app_switch_count,
        ])->values()->toJson();

        // كتابة البيانات في ملف مؤقت لتفادي مشاكل الهروب في الويندوز
        $tempFile = storage_path('app/temp_ai_data.json');
        file_put_contents($tempFile, $data);

        // استدعاء ملف البايثون
        $pythonPath = 'python'; // أو مسار بايثون الكامل إذا لزم الأمر
        $scriptPath = base_path('ai_engine/detect_anomalies.py');
        
        $escapedPath = escapeshellarg($tempFile);
        $command = "{$pythonPath} {$scriptPath} --file {$escapedPath}";
        
        $output = shell_exec($command);
        @unlink($tempFile);
        
        $results = json_decode($output, true);

        if (isset($results['status']) && $results['status'] === 'error') {
            Notification::make()->title('خطأ في محرك الذكاء الاصطناعي')->danger()->body($results['message'])->send();
            return;
        }

        if (is_array($results)) {
            foreach ($results as $res) {
                $analytic = VideoAnalytic::find($res['id']);
                if ($analytic) {
                    $analytic->update([
                        'is_anomaly' => $res['anomaly_prediction'] == -1,
                        'anomaly_score' => $res['anomaly_score'],
                    ]);
                }
            }

            Notification::make()
                ->title(__('admin.messages.analysis_completed'))
                ->success()
                ->send();
        } else {
            Notification::make()->title('فشل في استلام نتائج صالحة')->danger()->send();
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVideoAnalytics::route('/'),
        ];
    }
}
