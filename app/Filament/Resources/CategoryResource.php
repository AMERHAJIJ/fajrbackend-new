<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    public static function getNavigationLabel(): string { return __('admin.resources.category.plural_label'); }
    public static function getModelLabel(): string { return __('admin.resources.category.label'); }
    public static function getPluralModelLabel(): string { return __('admin.resources.category.plural_label'); }
    public static function getNavigationGroup(): ?string { return __('admin.navigation_group.content_management'); }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات التصنيف')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم التصنيف')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder(__('admin.placeholders.category_name')),
                        Forms\Components\TextInput::make('icon')
                            ->label(__('admin.fields.icon'))
                            ->maxLength(255)
                            ->placeholder('heroicon-o-book-open')
                            ->helperText(__('admin.helpers.icon_name'))
                            ->suffixIcon('heroicon-o-information-circle'),
                        Forms\Components\Toggle::make('active')
                            ->label(__('admin.fields.active'))
                            ->default(true)
                            ->helperText(__('admin.helpers.active_status')),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('icon')
                    ->label(__('admin.fields.icon'))
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => $state ?? __('admin.messages.no_icon')),
                Tables\Columns\IconColumn::make('active')
                    ->label(__('admin.fields.active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('blogs_count')
                    ->label('عدد المقالات')
                    ->counts('blogs')
                    ->badge()
                    ->color('primary')
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
                Tables\Actions\EditAction::make()
                    ->label('Düzenle'),
                Tables\Actions\DeleteAction::make()
                    ->label('Sil')
                    ->before(function ($record) {
                        if ($record->blogs()->count() > 0) {
                            throw new \Exception('لا يمكن حذف التصنيف لأنه يحتوي على مقالات');
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Seçilenleri Sil')
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->blogs()->count() > 0) {
                                    throw new \Exception('لا يمكن حذف بعض التصنيفات لأنها تحتوي على مقالات');
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create_categories');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('edit_categories');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->can('delete_categories');
    }

    public static function canView($record): bool
    {
        return auth()->user()->can('view_categories');
    }
}
