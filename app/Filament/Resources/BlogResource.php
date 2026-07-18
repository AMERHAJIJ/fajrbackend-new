<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogResource\Pages;
use App\Models\Blog;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class BlogResource extends Resource
{
    protected static ?string $model = Blog::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    public static function getNavigationLabel(): string { return __('admin.resources.blog.plural_label'); }
    public static function getModelLabel(): string { return __('admin.resources.blog.label'); }
    public static function getPluralModelLabel(): string { return __('admin.resources.blog.plural_label'); }
    public static function getNavigationGroup(): ?string { return __('admin.navigation_group.content_management'); }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('admin.resources.blog.label'))
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label(__('admin.fields.title'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('category_id')
                            ->label(__('admin.resources.category.label'))
                            ->relationship('category', 'name')
                            ->required(),
                        Forms\Components\RichEditor::make('content')
                            ->label(__('admin.fields.content'))
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('image')
                            ->label(__('admin.fields.image'))
                            ->image()
                            ->directory('blog'),
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
                    ->label(__('admin.fields.image')),
                Tables\Columns\TextColumn::make('visits')
                    ->label(__('admin.fields.views'))
                    ->sortable(),
                Tables\Columns\IconColumn::make('showInHomePage')
                    ->label(__('admin.fields.home_page'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('active')
                    ->label(__('admin.fields.active'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label(__('admin.fields.active'))
                    ->boolean(),
                Tables\Filters\TernaryFilter::make('showInHomePage')
                    ->label(__('admin.fields.home_page'))
                    ->boolean(),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label(__('admin.resources.category.label'))
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
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
            'index' => Pages\ListBlogs::route('/'),
            'create' => Pages\CreateBlog::route('/create'),
            'view' => Pages\ViewBlog::route('/{record}'),
            'edit' => Pages\EditBlog::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view blogs');
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        // can() method is provided by Spatie Permission HasRoles trait
        return $user && $user->can('create blogs');
    }

    public static function canEdit($record): bool
    {
        $user = Auth::user();
        // can() method is provided by Spatie Permission HasRoles trait
        return $user && $user->can('edit blogs');
    }

    public static function canDelete($record): bool
    {
        $user = Auth::user();
        // can() method is provided by Spatie Permission HasRoles trait
        return $user && $user->can('delete blogs');
    }
}
