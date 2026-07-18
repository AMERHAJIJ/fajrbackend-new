<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SliderResource\Pages;
use App\Filament\Resources\SliderResource\RelationManagers;
use App\Models\Slider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SliderResource extends Resource
{
    protected static ?string $model = Slider::class;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';
    public static function getNavigationLabel(): string { return __('admin.resources.slider.plural_label'); }
    public static function getModelLabel(): string { return __('admin.resources.slider.label'); }
    public static function getPluralModelLabel(): string { return __('admin.resources.slider.plural_label'); }
    public static function getNavigationGroup(): ?string { return __('admin.navigation_group.content_management'); }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('admin.resources.slider.label'))
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label(__('admin.fields.title'))
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('image')
                            ->label(__('admin.fields.image'))
                            ->image()
                            ->imageEditor()
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9')
                            ->imageResizeTargetWidth('1920')
                            ->imageResizeTargetHeight('1080')
                            ->directory('sliders')
                            ->required(),
                        Forms\Components\TextInput::make('link')
                            ->label(__('admin.fields.link'))
                            ->required()
                            ->url()
                            ->maxLength(255),
                        Forms\Components\Toggle::make('active')
                            ->label(__('admin.fields.active'))
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Resim')
                    ->height(80)
                    ->width(120),
                Tables\Columns\TextColumn::make('title')
                    ->label('Adres')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->placeholder('بدون عنوان'),
                Tables\Columns\TextColumn::make('link')
                    ->label('Bağlantı')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function ($record) {
                        return $record->link;
                    }),
                Tables\Columns\IconColumn::make('active')
                    ->label('Durum')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Oluşturulma Tarihi')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Durum')
                    ->boolean()
                    ->trueLabel('نشط فقط')
                    ->falseLabel('غير نشط فقط')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label('معاينة')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => $record->link)
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make()
                    ->label('Düzenle'),
                Tables\Actions\DeleteAction::make()
                    ->label('Sil'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Seçilenleri Sil'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->reorderable('id');
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
            'index' => Pages\ListSliders::route('/'),
            'create' => Pages\CreateSlider::route('/create'),
            'edit' => Pages\EditSlider::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create_sliders');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('edit_sliders');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->can('delete_sliders');
    }

    public static function canView($record): bool
    {
        return auth()->user()->can('view_sliders');
    }
}
