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

class BlogResource extends Resource
{
    protected static ?string $model = Blog::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'المقالات';

    protected static ?string $modelLabel = 'مقال';

    protected static ?string $pluralModelLabel = 'المقالات';

    protected static ?string $navigationGroup = 'إدارة المحتوى';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات المقال')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('عنوان المقال')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('category_id')
                            ->label('الفئة')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\FileUpload::make('image')
                            ->label('صورة المقال')
                            ->image()
                            ->imageEditor()
                            ->directory('blogs')
                            ->nullable(),
                    ])->columns(2),

                Forms\Components\Section::make('محتوى المقال')
                    ->schema([
                        Forms\Components\RichEditor::make('content')
                            ->label('المحتوى')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('الإعدادات')
                    ->schema([
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
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('الصورة')
                    ->circular(),
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان المقال')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('الفئة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('content')
                    ->label('المحتوى')
                    ->html()
                    ->limit(100)
                    ->searchable(),
                Tables\Columns\TextColumn::make('visits')
                    ->label('المشاهدات')
                    ->sortable(),
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
                Tables\Filters\TernaryFilter::make('showInHomePage')
                    ->label('الصفحة الرئيسية')
                    ->boolean()
                    ->trueLabel('يظهر في الرئيسية')
                    ->falseLabel('لا يظهر في الرئيسية'),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('الفئة')
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
        return true; // جميع المستخدمين يمكنهم رؤية المقالات
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create blogs');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('edit blogs');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->can('delete blogs');
    }
}
