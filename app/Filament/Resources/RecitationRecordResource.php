<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecitationRecordResource\Pages;
use App\Models\RecitationRecord;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Surah;
use App\Filament\Resources\RecitationRecordResource\Actions\ExportToWhatsAppAction;

class RecitationRecordResource extends Resource
{
    protected static ?string $model = RecitationRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-microphone';
    public static function getNavigationLabel(): string { return __('admin.resources.recitation_record.plural_label'); }
    public static function getModelLabel(): string { return __('admin.resources.recitation_record.label'); }
    public static function getPluralModelLabel(): string { return __('admin.resources.recitation_record.plural_label'); }
    public static function getNavigationGroup(): ?string { return __('admin.navigation_group.education_management'); }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('admin.resources.recitation_record.label'))
                    ->schema([
                        Forms\Components\Select::make('subject_id')
                            ->label('الحلقة')
                            ->options(function () {
                                $user = auth()->user();
                                if ($user->hasRole('teacher')) {
                                    return \App\Models\Subject::where('is_quran', true)
                                        ->whereHas('teachers', function ($q) use ($user) {
                                            $q->where('teacher_id', $user->id);
                                        })
                                        ->pluck('title', 'id');
                                }
                                return \App\Models\Subject::where('is_quran', true)->pluck('title', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('student_id', null)),

                        Forms\Components\Select::make('student_id')
                            ->label(__('admin.fields.student'))
                            ->options(function (callable $get) {
                                $subjectId = $get('subject_id');
                                if (!$subjectId) {
                                    return [];
                                }
                                return \App\Models\User::role('student')
                                    ->whereHas('subjectsAsStudent', function ($q) use ($subjectId) {
                                        $q->where('subject_id', $subjectId);
                                    })
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\DatePicker::make('date')
                            ->label(__('admin.fields.date'))
                            ->required()
                            ->default(now())
                            ->maxDate(now()),
                        Forms\Components\Hidden::make('teacher_id')
                            ->default(auth()->id()),

                        Forms\Components\Repeater::make('surahs')
                            ->label(__('admin.resources.surah.plural_label'))
                            ->relationship('surahs')
                            ->schema([
                                Forms\Components\Select::make('id')
                                    ->label(__('admin.resources.surah.label'))
                                    ->relationship('surah', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->optionsLimit(114)
                                    ->required()
                                    ->columnSpan(1)
                                    ->getOptionLabelFromRecordUsing(fn (Surah $record) => $record->id . ' - ' . $record->name)
                                    ->options(
                                        Surah::orderBy('id')
                                            ->pluck('name', 'id')
                                            ->mapWithKeys(fn ($name, $id) => [$id => $id . ' - ' . $name])
                                    ),
                                
                                Forms\Components\Select::make('pivot.type')
                                    ->label(__('admin.fields.type'))
                                    ->options([
                                        'ayah' => __('admin.options.ayah_range'),
                                        'page' => __('admin.options.page_range')
                                    ])
                                    ->default('ayah')
                                    ->live()
                                    ->required()
                                    ->columnSpan(1),
                                
                                // حقل مشروط للآيات
                                Forms\Components\Group::make([
                                    Forms\Components\TextInput::make('pivot.fromAyeh')
                                        ->label(__('admin.fields.from_ayah'))
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(286)
                                        ->default(1)
                                        ->visible(fn($get) => $get('pivot.type') === 'ayah')
                                        ->required(fn($get) => $get('pivot.type') === 'ayah'),
                                    Forms\Components\TextInput::make('pivot.toAyeh')
                                        ->label(__('admin.fields.to_ayah'))
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(286)
                                        ->default(1)
                                        ->visible(fn($get) => $get('pivot.type') === 'ayah')
                                        ->required(fn($get) => $get('pivot.type') === 'ayah')
                                        ->gt('pivot.fromAyeh'),
                                ])->columns(2),
                                
                                // حقل مشروط للصفحات
                                Forms\Components\Group::make([
                                    Forms\Components\TextInput::make('pivot.fromPage')
                                        ->label(__('admin.fields.from_page'))
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(604)
                                        ->default(1)
                                        ->visible(fn($get) => $get('pivot.type') === 'page')
                                        ->required(fn($get) => $get('pivot.type') === 'page'),
                                    Forms\Components\TextInput::make('pivot.toPage')
                                        ->label(__('admin.fields.to_page'))
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(604)
                                        ->default(1)
                                        ->visible(fn($get) => $get('pivot.type') === 'page')
                                        ->required(fn($get) => $get('pivot.type') === 'page')
                                        ->gt('pivot.fromPage'),
                                ])->columns(2),
                            ])
                            ->columns(1)
                            ->defaultItems(1)
                            ->minItems(1)
                            ->addActionLabel(__('admin.actions.add_another_surah'))
                            ->reorderable()
                            ->columnSpanFull()
                            ->createItemButtonLabel(__('admin.actions.add_surah'))
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 
                                isset($state['id']) 
                                    ? Surah::find($state['id'])?->name . ' (' . __('admin.sections.from') . ' ' . ($state['pivot']['fromAyeh'] ?? 1) . ' ' . __('admin.sections.to') . ' ' . ($state['pivot']['toAyeh'] ?? 1) . ')'
                                    : null
                            )
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                $pivotData = [
                                    'type' => $data['pivot']['type'],
                                ];
                                
                                if ($data['pivot']['type'] === 'ayah') {
                                    $pivotData['fromAyeh'] = $data['pivot']['fromAyeh'];
                                    $pivotData['toAyeh'] = $data['pivot']['toAyeh'];
                                } else {
                                    $pivotData['fromPage'] = $data['pivot']['fromPage'];
                                    $pivotData['toPage'] = $data['pivot']['toPage'];
                                }
                                
                                return $pivotData;
                            })
                            ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                                $pivotData = [
                                    'type' => $data['pivot']['type'],
                                ];
                                
                                if ($data['pivot']['type'] === 'ayah') {
                                    $pivotData['fromAyeh'] = $data['pivot']['fromAyeh'];
                                    $pivotData['toAyeh'] = $data['pivot']['toAyeh'];
                                } else {
                                    $pivotData['fromPage'] = $data['pivot']['fromPage'];
                                    $pivotData['toPage'] = $data['pivot']['toPage'];
                                }
                                
                                return $pivotData;
                            })
                            ->saveRelationshipsUsing(function (RecitationRecord $record, array $state) {
                                $record->surahs()->sync(
                                    collect($state)
                                        ->mapWithKeys(function ($item) {
                                            $pivotData = [
                                                'type' => $item['pivot']['type'],
                                            ];
                                            
                                            if ($item['pivot']['type'] === 'ayah') {
                                                $pivotData['fromAyeh'] = $item['pivot']['fromAyeh'];
                                                $pivotData['toAyeh'] = $item['pivot']['toAyeh'];
                                            } else {
                                                $pivotData['fromPage'] = $item['pivot']['fromPage'];
                                                $pivotData['toPage'] = $item['pivot']['toPage'];
                                            }
                                            
                                            return [$item['id'] => $pivotData];
                                        })
                                );
                            }),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('التقييم')
                    ->schema([
                        Forms\Components\TextInput::make('score')
                            ->label('الدرجة')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.5)
                            ->suffix('%'),
                    ])
                    ->columns(1),
            ]);
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
                    ->label('الحلقة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('surahs')
                    ->label(__('admin.resources.surah.plural_label'))
                    ->formatStateUsing(function ($record) {
                        return $record->surahs->map(function ($surah) {
                            $type = $surah->pivot->type === 'ayah' ? __('admin.fields.from_ayah') : __('admin.fields.from_page');
                            if ($surah->pivot->type === 'ayah') {
                                return $surah->name . ' (من آية ' . $surah->pivot->fromAyeh . ' إلى ' . $surah->pivot->toAyeh . ')';
                            } else {
                                return $surah->name . ' (من صفحة ' . $surah->pivot->fromPage . ' إلى ' . $surah->pivot->toPage . ')';
                            }
                        })->implode('، ');
                    })
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('date')
                    ->label(__('admin.fields.date'))
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('score')
                    ->label(__('admin.fields.score'))
                    ->suffix('%')
                    ->sortable()
                    ->color(fn ($state) => $state >= 80 ? 'success' : ($state >= 60 ? 'warning' : 'danger')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subject_id')
                    ->label('الحلقة')
                    ->options(function () {
                        $user = auth()->user();
                        if ($user->hasRole('teacher')) {
                            return \App\Models\Subject::where('is_quran', true)
                                ->whereHas('teachers', function ($q) use ($user) {
                                    $q->where('teacher_id', $user->id);
                                })
                                ->pluck('title', 'id');
                        }
                        return \App\Models\Subject::where('is_quran', true)->pluck('title', 'id');
                    })
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('student')
                    ->label(__('admin.fields.student'))
                    ->relationship('student', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('surah')
                    ->label(__('admin.fields.surah'))
                    ->relationship('surahs', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('admin.fields.type'))
                    ->options([
                        'ayah' => __('admin.fields.from_ayah'),
                        'page' => __('admin.fields.from_page')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!$data['value']) {
                            return $query;
                        }
                        return $query->whereHas('surahs', function ($q) use ($data) {
                            $q->where('recitation_record_surah.type', $data['value']);
                        });
                    }),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Başlangıç Tarihi'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Bitiş Tarihi'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                ExportToWhatsAppAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListRecitationRecords::route('/'),
            'create' => Pages\CreateRecitationRecord::route('/create'),
            'edit' => Pages\EditRecitationRecord::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->hasRole('student')) {
            return $query->where('student_id', auth()->id());
        }

        if (auth()->user()->hasRole('teacher')) {
            return $query->whereHas('student', function ($query) {
                $query->whereHas('subjectsAsStudent', function ($query) {
                    $query->whereHas('teachers', function ($query) {
                        $query->where('users.id', auth()->id());
                    });
                });
            });
        }

        return $query;
    }
}
