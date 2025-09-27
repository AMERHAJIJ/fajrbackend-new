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
    protected static ?string $navigationLabel = 'تسجيلات التلاوة';
    protected static ?string $modelLabel = 'تسجيل تلاوة';
    protected static ?string $pluralModelLabel = 'تسجيلات التلاوة';
    protected static ?string $navigationGroup = 'إدارة التعليم';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات التسجيل')
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->label('الطالب')
                            ->relationship('student', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->options(function () {
                                if (auth()->user()->hasRole('student')) {
                                    return [auth()->id() => auth()->user()->name];
                                }
                                
                                if (auth()->user()->hasRole('teacher')) {
                                    return User::role('student')
                                        ->whereHas('subjectsAsStudent', function($query) {
                                            $query->whereHas('teachers', function($q) {
                                                $q->where('teacher_id', auth()->id());
                                            });
                                        })
                                        ->pluck('name', 'id');
                                }
                                
                                return User::role('student')->pluck('name', 'id');
                            })
                            ->default(auth()->user()->hasRole('student') ? auth()->id() : null),
                        
                        Forms\Components\DatePicker::make('date')
                            ->label('تاريخ التسجيل')
                            ->required()
                            ->default(now())
                            ->maxDate(now()),

                        Forms\Components\Repeater::make('surahs')
                            ->label('السور')
                            ->relationship('surahs')
                            ->schema([
                                Forms\Components\Select::make('id')  // Changed from surah_id to id
                                    ->label('السورة')
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
                                Forms\Components\TextInput::make('pivot.fromAyeh')
                                    ->label('من آية')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(286)
                                    ->default(1)
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('pivot.toAyeh')
                                    ->label('إلى آية')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(286)
                                    ->default(1)
                                    ->columnSpan(1)
                                    ->gt('pivot.fromAyeh'),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->minItems(1)
                            ->addActionLabel('إضافة سورة أخرى')
                            ->reorderable()
                            ->columnSpanFull()
                            ->createItemButtonLabel('إضافة سورة')
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 
                                isset($state['id']) 
                                    ? Surah::find($state['id'])?->name . ' (من ' . ($state['pivot']['fromAyeh'] ?? 1) . ' إلى ' . ($state['pivot']['toAyeh'] ?? 1) . ')'
                                    : null
                            )
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                return [
                                    'fromAyeh' => $data['pivot']['fromAyeh'],
                                    'toAyeh' => $data['pivot']['toAyeh'],
                                ];
                            })
                            ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                                return [
                                    'fromAyeh' => $data['pivot']['fromAyeh'],
                                    'toAyeh' => $data['pivot']['toAyeh'],
                                ];
                            })
                            ->saveRelationshipsUsing(function (RecitationRecord $record, array $state) {
                                $record->surahs()->sync(
                                    collect($state)
                                        ->mapWithKeys(function ($item) {
                                            return [
                                                $item['id'] => [
                                                    'fromAyeh' => $item['pivot']['fromAyeh'],
                                                    'toAyeh' => $item['pivot']['toAyeh'],
                                                ]
                                            ];
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
                    ->label('الطالب')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('surahs')
                    ->label('السور')
                    ->formatStateUsing(function ($record) {
                        return $record->surahs->map(function ($surah) {
                            return $surah->name . ' (من ' . $surah->pivot->fromAyeh . ' إلى ' . $surah->pivot->toAyeh . ')';
                        })->implode('، ');
                    })
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('score')
                    ->label('الدرجة')
                    ->suffix('%')
                    ->sortable()
                    ->color(fn ($state) => $state >= 80 ? 'success' : ($state >= 60 ? 'warning' : 'danger')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('student')
                    ->label('الطالب')
                    ->relationship('student', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('surah')
                    ->label('السورة')
                    ->relationship('surahs', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('من تاريخ'),
                        Forms\Components\DatePicker::make('until')
                            ->label('إلى تاريخ'),
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
                        $query->where('teacher_id', auth()->id());
                    });
                });
            });
        }

        return $query;
    }
}
