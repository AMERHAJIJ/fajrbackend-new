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

    protected static ?string $navigationLabel = 'الفيديوهات';

    protected static ?string $modelLabel = 'فيديو';

    protected static ?string $pluralModelLabel = 'الفيديوهات';

    protected static ?string $navigationGroup = 'إدارة المحتوى';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الفيديو')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم الفيديو')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('link')
                            ->label('رابط الفيديو')
                            ->url()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('image')
                            ->label('صورة الفيديو')
                            ->image()
                            ->imageEditor()
                            ->directory('videos')
                            ->nullable(),
                    ])->columns(2),

                Forms\Components\Section::make('التصنيف')
                    ->schema([
                        Forms\Components\Select::make('object_type')
                            ->label('نوع التصنيف')
                            ->options([
                                'App\\Models\\Category' => 'فئة',
                                'App\\Models\\Subject' => 'مادة دراسية',
                            ])
                            ->required()
                            ->reactive(),
                        Forms\Components\Select::make('object_id')
                            ->label('التصنيف')
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

                Forms\Components\Section::make('الإعدادات')
                    ->schema([
                        Forms\Components\Toggle::make('isRequired')
                            ->label('مطلوب')
                            ->default(false),
                        Forms\Components\Toggle::make('showInHomePage')
                            ->label('عرض في الصفحة الرئيسية')
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
                    ->label('الصورة')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الفيديو')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('object_type')
                    ->label('نوع التصنيف')
                    ->formatStateUsing(fn ($state) => $state === 'App\\Models\\Category' ? 'فئة' : 'مادة دراسية'),
                Tables\Columns\TextColumn::make('object.name')
                    ->label('التصنيف')
                    ->searchable(),
                Tables\Columns\TextColumn::make('visits')
                    ->label('المشاهدات')
                    ->sortable(),
                Tables\Columns\IconColumn::make('isRequired')
                    ->label('مطلوب')
                    ->boolean(),
                Tables\Columns\IconColumn::make('showInHomePage')
                    ->label('الصفحة الرئيسية')
                    ->boolean(),
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
                Tables\Filters\TernaryFilter::make('isRequired')
                    ->label('مطلوب')
                    ->boolean()
                    ->trueLabel('مطلوب فقط')
                    ->falseLabel('غير مطلوب فقط'),
                Tables\Filters\TernaryFilter::make('showInHomePage')
                    ->label('الصفحة الرئيسية')
                    ->boolean()
                    ->trueLabel('يظهر في الرئيسية')
                    ->falseLabel('لا يظهر في الرئيسية'),
                Tables\Filters\SelectFilter::make('object_type')
                    ->label('نوع التصنيف')
                    ->options([
                        'App\\Models\\Category' => 'فئة',
                        'App\\Models\\Subject' => 'مادة دراسية',
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
