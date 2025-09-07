<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecitationRecordResource\Pages;
use App\Models\RecitationRecord;
use App\Models\Surah;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                                return User::role('student')->pluck('name', 'id');
                            })
                            ->default(auth()->user()->hasRole('student') ? auth()->id() : null),
                        Forms\Components\Select::make('surah_id')
                            ->label('السورة')
                            ->relationship('surah', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\DatePicker::make('date')
                            ->label('تاريخ التسجيل')
                            ->required()
                            ->default(now())
                            ->maxDate(now()),
                    ])->columns(3),

                Forms\Components\Section::make('تفاصيل التلاوة')
                    ->schema([
                        Forms\Components\TextInput::make('fromAyeh')
                            ->label('من آية')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(286),
                        Forms\Components\TextInput::make('toAyeh')
                            ->label('إلى آية')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(286)
                            ->gte('fromAyeh'),
                        Forms\Components\TextInput::make('score')
                            ->label('الدرجة')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.5)
                            ->suffix('%'),
                    ])->columns(3),
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
                Tables\Columns\TextColumn::make('surah.name')
                    ->label('السورة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fromAyeh')
                    ->label('من آية')
                    ->sortable(),
                Tables\Columns\TextColumn::make('toAyeh')
                    ->label('إلى آية')
                    ->sortable(),
                Tables\Columns\TextColumn::make('score')
                    ->label('الدرجة')
                    ->suffix('%')
                    ->sortable()
                    ->color(fn ($state) => $state >= 80 ? 'success' : ($state >= 60 ? 'warning' : 'danger')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('surah_id')
                    ->label('السورة')
                    ->relationship('surah', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('score')
                    ->form([
                        Forms\Components\TextInput::make('min_score')
                            ->label('أقل درجة')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                        Forms\Components\TextInput::make('max_score')
                            ->label('أعلى درجة')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_score'],
                                fn (Builder $query, $score): Builder => $query->where('score', '>=', $score),
                            )
                            ->when(
                                $data['max_score'],
                                fn (Builder $query, $score): Builder => $query->where('score', '<=', $score),
                            );
                    }),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from_date')
                            ->label('من تاريخ'),
                        Forms\Components\DatePicker::make('to_date')
                            ->label('إلى تاريخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['to_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // إذا كان المستخدم طالب، عرض تسجيلاته فقط
        if (auth()->user()->hasRole('student')) {
            $query->where('student_id', auth()->id());
        }
        
        return $query;
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
            'view' => Pages\ViewRecitationRecord::route('/{record}'),
            'edit' => Pages\EditRecitationRecord::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'teacher']);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasRole(['admin', 'teacher']);
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->hasRole(['admin', 'teacher']);
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->hasRole('admin');
    }
}
