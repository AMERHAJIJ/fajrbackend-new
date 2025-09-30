<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SurahResource\Pages;
use App\Models\Surah;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SurahResource extends Resource
{
    protected static ?string $model = Surah::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'السور القرآنية';

    protected static ?string $modelLabel = 'سورة';

    protected static ?string $pluralModelLabel = 'السور القرآنية';

    protected static ?string $navigationGroup = 'إدارة التلاوة';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات السورة')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم السورة')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('number')
                            ->label('رقم السورة')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(114),
                        Forms\Components\TextInput::make('verses_count')
                            ->label('عدد الآيات')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        Forms\Components\Select::make('type')
                            ->label('نوع السورة')
                            ->options([
                                'مكية' => 'مكية',
                                'مدنية' => 'مدنية',
                            ])
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('معلومات إضافية')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('وصف السورة')
                            ->rows(3)
                            ->nullable(),
                        Forms\Components\Toggle::make('active')
                            ->label('نشط')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('الرقم')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم السورة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('verses_count')
                    ->label('عدد الآيات')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'مكية' => 'success',
                        'مدنية' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('recitationRecords_count')
                    ->label('عدد التسجيلات')
                    ->counts('recitationRecords')
                    ->sortable(),
                Tables\Columns\IconColumn::make('active')
                    ->label('نشط')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label('نشط')
                    ->boolean()
                    ->trueLabel('نشط فقط')
                    ->falseLabel('غير نشط فقط'),
                Tables\Filters\SelectFilter::make('type')
                    ->label('نوع السورة')
                    ->options([
                        'مكية' => 'مكية',
                        'مدنية' => 'مدنية',
                    ]),
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
            ->defaultSort('number', 'asc');
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
            'index' => Pages\ListSurahs::route('/'),
            'create' => Pages\CreateSurah::route('/create'),
            'view' => Pages\ViewSurah::route('/{record}'),
            'edit' => Pages\EditSurah::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view surahs');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create surahs');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('edit surahs');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->can('delete surahs');
    }
}
