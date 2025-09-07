<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FileResource\Pages;
use App\Models\File;
use App\Models\Category;
use App\Models\Video;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FileResource extends Resource
{
    protected static ?string $model = File::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-down';

    protected static ?string $navigationLabel = 'الملفات';

    protected static ?string $modelLabel = 'ملف';

    protected static ?string $pluralModelLabel = 'الملفات';

    protected static ?string $navigationGroup = 'إدارة المحتوى';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الملف')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم الملف')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label('وصف الملف')
                            ->maxLength(1000),
                        Forms\Components\FileUpload::make('image')
                            ->label('صورة الملف')
                            ->image()
                            ->imageEditor()
                            ->directory('file-images')
                            ->nullable(),
                        Forms\Components\FileUpload::make('link')
                            ->label('الملف')
                            ->directory('files')
                            ->required()
                            ->acceptedFileTypes(['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'rar']),
                    ])->columns(2),

                Forms\Components\Section::make('التصنيف')
                    ->schema([
                        Forms\Components\Select::make('object_type')
                            ->label('نوع التصنيف')
                            ->options([
                                'App\\Models\\Category' => 'فئة',
                                'App\\Models\\Video' => 'فيديو',
                            ])
                            ->required()
                            ->reactive(),
                        Forms\Components\Select::make('object_id')
                            ->label('التصنيف')
                            ->options(function (callable $get) {
                                $type = $get('object_type');
                                if ($type === 'App\\Models\\Category') {
                                    return Category::pluck('name', 'id');
                                } elseif ($type === 'App\\Models\\Video') {
                                    return Video::pluck('name', 'id');
                                }
                                return [];
                            })
                            ->required()
                            ->reactive(),
                    ])->columns(2),

                Forms\Components\Section::make('الإعدادات')
                    ->schema([
                        Forms\Components\Toggle::make('showInHomePage')
                            ->label('عرض في الصفحة الرئيسية')
                            ->default(false),
                        Forms\Components\Toggle::make('active')
                            ->label('نشط')
                            ->default(true),
                        Forms\Components\TextInput::make('visits')
                            ->label('عدد التحميلات')
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
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الملف')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('object_type')
                    ->label('نوع التصنيف')
                    ->formatStateUsing(fn ($state) => $state === 'App\\Models\\Category' ? 'فئة' : 'فيديو'),
                Tables\Columns\TextColumn::make('object.name')
                    ->label('التصنيف')
                    ->searchable(),
                Tables\Columns\TextColumn::make('visits')
                    ->label('التحميلات')
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
                Tables\Filters\SelectFilter::make('object_type')
                    ->label('نوع التصنيف')
                    ->options([
                        'App\\Models\\Category' => 'فئة',
                        'App\\Models\\Video' => 'فيديو',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('download')
                    ->label('تحميل')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record) => asset('storage/' . $record->link))
                    ->openUrlInNewTab(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFiles::route('/'),
            'create' => Pages\CreateFile::route('/create'),
            'view' => Pages\ViewFile::route('/{record}'),
            'edit' => Pages\EditFile::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return true; // جميع المستخدمين يمكنهم رؤية الملفات
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
