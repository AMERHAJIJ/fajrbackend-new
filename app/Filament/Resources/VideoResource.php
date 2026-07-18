<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VideoResource\Pages;
use App\Filament\Resources\VideoResource\RelationManagers;
use App\Models\Video;
use App\Models\Category;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VideoResource extends Resource
{
    protected static ?string $model = Video::class;

    protected static ?string $navigationIcon = 'heroicon-o-video-camera';

    public static function getNavigationLabel(): string { return __('admin.resources.video.plural_label'); }
    public static function getModelLabel(): string { return __('admin.resources.video.label'); }
    public static function getPluralModelLabel(): string { return __('admin.resources.video.plural_label'); }
    public static function getNavigationGroup(): ?string { return __('admin.navigation_group.content_management'); }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('admin.resources.video.label'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('admin.fields.title'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('link')
                            ->label(__('admin.fields.link'))
                            ->url()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('image')
                            ->label(__('admin.fields.image'))
                            ->image()
                            ->imageEditor()
                            ->directory('videos')
                            ->nullable(),
                    ])->columns(2),

                Forms\Components\Section::make(__('admin.resources.category.label'))
                    ->schema([
                        Forms\Components\Select::make('object_type')
                            ->label(__('admin.fields.type'))
                            ->options([
                                'App\\Models\\Category' => __('admin.resources.category.label'),
                                'App\\Models\\Subject' => __('admin.resources.subject.label'),
                            ])
                            ->required()
                            ->reactive(),
                        Forms\Components\Select::make('object_id')
                            ->label(__('admin.resources.category.label'))
                            ->options(function (callable $get) {
                                $type = $get('object_type');
                                if ($type === 'App\\Models\\Category') {
                                    return Category::pluck('name', 'id');
                                } elseif ($type === 'App\\Models\\Subject') {
                                    return Subject::pluck('title', 'id');
                                }
                                return [];
                            })
                            ->required()
                            ->reactive(),
                    ])->columns(2),

                Forms\Components\Section::make('Ayarlar')
                    ->schema([
                        Forms\Components\Toggle::make('isRequired')
                            ->label('مطلوب')
                            ->default(false),
                        Forms\Components\Toggle::make('showInHomePage')
                            ->label('Ana Sayfada Göster')
                            ->default(false),
                        Forms\Components\Toggle::make('active')
                            ->label('نشط')
                            ->default(true),
                        Forms\Components\TextInput::make('visits')
                            ->label('عدد المشاهدات')
                            ->numeric()
                            ->default(0)
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Resim')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الفيديو')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('object_type')
                    ->label('Sınıflandırma Türü')
                    ->formatStateUsing(fn ($state) => $state === 'App\\Models\\Category' ? 'Kategori' : 'Ders'),
                Tables\Columns\TextColumn::make('object.name')
                    ->label('Sınıflandırma')
                    ->searchable(),
                Tables\Columns\TextColumn::make('visits')
                    ->label('Görüntülenme')
                    ->sortable(),
                Tables\Columns\IconColumn::make('isRequired')
                    ->label('مطلوب')
                    ->boolean(),
                Tables\Columns\IconColumn::make('showInHomePage')
                    ->label('Ana Sayfa')
                    ->boolean(),
                Tables\Columns\IconColumn::make('active')
                    ->label('نشط')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Oluşturulma Tarihi')
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
                Tables\Filters\TernaryFilter::make('isRequired')
                    ->label('مطلوب')
                    ->boolean()
                    ->trueLabel('مطلوب فقط')
                    ->falseLabel('غير مطلوب فقط'),
                Tables\Filters\TernaryFilter::make('showInHomePage')
                    ->label('Ana Sayfa')
                    ->boolean()
                    ->trueLabel('يظهر في الرئيسية')
                    ->falseLabel('لا يظهر في الرئيسية'),
                Tables\Filters\SelectFilter::make('object_type')
                    ->label('Sınıflandırma Türü')
                    ->options([
                        'App\\Models\\Category' => 'Kategori',
                        'App\\Models\\Subject' => 'Ders',
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
            ->defaultSort('visits', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SeenVideosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVideos::route('/'),
            'create' => Pages\CreateVideo::route('/create'),
            'view' => Pages\ViewVideo::route('/{record}'),
            'edit' => Pages\EditVideo::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return true; // جميع المستخدمين يمكنهم رؤية الفيديوهات
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create videos');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('edit videos');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->can('delete videos');
    }
}
