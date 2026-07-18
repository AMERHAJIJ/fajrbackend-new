<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TripResource\Pages;
use App\Filament\Resources\TripResource\RelationManagers;
use App\Filament\Resources\TripResource\Widgets;
use App\Models\Trip;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TripResource extends Resource
{
    protected static ?string $model = Trip::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';
    
    public static function getNavigationGroup(): ?string { return __('admin.navigation_group.trip_management'); }
    
    public static function getNavigationLabel(): string { return __('admin.resources.trip.plural_label'); }
    
    public static function getModelLabel(): string { return __('admin.resources.trip.label'); }
    
    public static function getPluralModelLabel(): string { return __('admin.resources.trip.plural_label'); }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('admin.fields.trip_info'))
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label(__('admin.fields.trip_title'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('status')
                            ->label(__('admin.fields.trip_status'))
                            ->options([
                                'upcoming' => __('admin.fields.upcoming'),
                                'active' => __('admin.fields.active'),
                                'finished' => __('admin.fields.finished'),
                            ])
                            ->default('upcoming')
                            ->required(),
                        Forms\Components\DatePicker::make('date')
                            ->label(__('admin.fields.date')),
                        Forms\Components\TextInput::make('location')
                            ->label(__('admin.fields.location'))
                            ->maxLength(255),
                        Forms\Components\TimePicker::make('departure_time')
                            ->label(__('admin.fields.departure_time')),
                        Forms\Components\TimePicker::make('return_time')
                            ->label(__('admin.fields.return_time')),
                        Forms\Components\TextInput::make('capacity')
                            ->label(__('admin.fields.capacity'))
                            ->numeric(),
                        Forms\Components\FileUpload::make('image')
                            ->label(__('admin.fields.trip_image'))
                            ->image()
                            ->directory('trips')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('description')
                            ->label(__('admin.fields.description'))
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make(__('admin.fields.financial_management'))
                    ->schema([
                        Forms\Components\TextInput::make('bus_cost')
                            ->label(__('admin.fields.bus_cost'))
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                        Forms\Components\TextInput::make('food_cost')
                            ->label(__('admin.fields.food_cost'))
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                        Forms\Components\TextInput::make('entry_cost')
                            ->label(__('admin.fields.entry_cost'))
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                        Forms\Components\TextInput::make('additional_cost')
                            ->label(__('admin.fields.additional_cost'))
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                        Forms\Components\TextInput::make('other_expenses')
                            ->label(__('admin.fields.other_expenses'))
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                        Forms\Components\TextInput::make('cost_per_student')
                            ->label(__('admin.fields.cost_per_student'))
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->default(0)
                            ->helperText(__('admin.helpers.cost_per_student')),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Resim')
                    ->circular(),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('admin.fields.title'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label(__('admin.fields.date'))
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'upcoming' => 'gray',
                        'active' => 'success',
                        'finished' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'upcoming' => __('admin.fields.upcoming'),
                        'active' => __('admin.fields.active'),
                        'finished' => __('admin.fields.finished'),
                    }),
                Tables\Columns\TextColumn::make('participants_count')
                    ->label(__('admin.fields.participants'))
                    ->counts('participants'),
                Tables\Columns\TextColumn::make('cost_per_student')
                    ->label(__('admin.fields.fees'))
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->label(__('admin.fields.location'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Durum')
                    ->options([
                        'upcoming' => __('admin.fields.upcoming'),
                        'active' => __('admin.fields.active'),
                        'finished' => __('admin.fields.finished'),
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            RelationManagers\ParticipantsRelationManager::class,
            RelationManagers\BusesRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\TripStats::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrips::route('/'),
            'create' => Pages\CreateTrip::route('/create'),
            'view' => Pages\ViewTrip::route('/{record}'),
            'edit' => Pages\EditTrip::route('/{record}/edit'),
        ];
    }
}
