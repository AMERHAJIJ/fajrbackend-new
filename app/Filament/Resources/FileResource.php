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

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    public static function getNavigationLabel(): string { return __('admin.resources.file.plural_label'); }
    public static function getModelLabel(): string { return __('admin.resources.file.label'); }
    public static function getPluralModelLabel(): string { return __('admin.resources.file.plural_label'); }
    public static function getNavigationGroup(): ?string { return __('admin.navigation_group.content_management'); }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

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
                            ->label('Dosya')
                            ->directory('files')
                            ->required()
                            ->disk('public') // التأكد من استخدام القرص العام
                            ->maxSize(102400) // رفع الحد الأقصى إلى 100 ميجابايت
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-powerpoint',
                                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                                'text/plain',
                                'application/zip',
                                'application/x-rar-compressed',
                                'application/octet-stream', // يدعم الملفات المضغوطة أحياناً
                            ]),
                    ])->columns(2),

                Forms\Components\Section::make('الخصوصية والتصنيف')
                    ->schema([
                        Forms\Components\Toggle::make('is_public')
                            ->label('ملف عام (للمكتبة العامة)')
                            ->helperText('إذا تم تفعيله، سيظهر الملف لجميع زوار التطبيق')
                            ->default(true)
                            ->live(),
                        Forms\Components\Select::make('object_type')
                            ->label('مرتبط بـ')
                            ->options([
                                'App\\Models\\Category' => 'تصنيف عام',
                                'App\\Models\\Subject' => 'مادة دراسية (للمسجلين فقط)',
                                'App\\Models\\Video' => 'فيديو معين',
                            ])
                            ->required(fn (Forms\Get $get) => !$get('is_public')) // إجباري فقط إذا لم يكن عاماً
                            ->hidden(fn (Forms\Get $get) => $get('is_public')) // نخفيه إذا كان عاماً لتبسيط الواجهة
                            ->reactive(),
                        Forms\Components\Select::make('object_id')
                            ->label('اختر الهدف')
                            ->options(function (callable $get) {
                                $type = $get('object_type');
                                if ($type === 'App\\Models\\Category') {
                                    return Category::pluck('name', 'id');
                                } elseif ($type === 'App\\Models\\Subject') {
                                    return \App\Models\Subject::pluck('title', 'id');
                                } elseif ($type === 'App\\Models\\Video') {
                                    return Video::pluck('name', 'id');
                                }
                                return [];
                            })
                            ->required(fn (Forms\Get $get) => !$get('is_public'))
                            ->hidden(fn (Forms\Get $get) => $get('is_public'))
                            ->reactive(),
                    ])->columns(2),

                Forms\Components\Section::make('Ek Ayarlar')
                    ->schema([
                        Forms\Components\Toggle::make('showInHomePage')
                            ->label('Ana Sayfada Göster')
                            ->default(false),
                        Forms\Components\Toggle::make('active')
                            ->label('نشط')
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
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الملف')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Açıklama')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('object_type')
                    ->label('Sınıflandırma Türü')
                    ->formatStateUsing(fn ($state) => $state === 'App\\Models\\Category' ? 'Kategori' : 'Video'),
                Tables\Columns\TextColumn::make('object.name')
                    ->label('Sınıflandırma')
                    ->searchable(),
                Tables\Columns\TextColumn::make('visits')
                    ->label('التحميلات')
                    ->sortable(),
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
                Tables\Filters\TernaryFilter::make('showInHomePage')
                    ->label('Ana Sayfa')
                    ->boolean()
                    ->trueLabel('يظهر في الرئيسية')
                    ->falseLabel('لا يظهر في الرئيسية'),
                Tables\Filters\SelectFilter::make('object_type')
                    ->label('Sınıflandırma Türü')
                    ->options([
                        'App\\Models\\Category' => 'Kategori',
                        'App\\Models\\Video' => 'Video',
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
        return auth()->user()->can('view files');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create files');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('edit files');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->can('delete files');
    }
}
