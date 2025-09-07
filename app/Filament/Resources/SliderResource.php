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

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'العروض التقديمية';

    protected static ?string $modelLabel = 'عرض تقديمي';

    protected static ?string $pluralModelLabel = 'العروض التقديمية';

    protected static ?string $navigationGroup = 'إدارة المحتوى';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات العرض التقديمي')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('عنوان العرض')
                            ->maxLength(255)
                            ->placeholder('مثال: دورة تحفيظ جديدة - سجل الآن')
                            ->helperText('العنوان اختياري - يظهر على الصورة'),
                        Forms\Components\FileUpload::make('image')
                            ->label('صورة العرض')
                            ->image()
                            ->imageEditor()
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9')
                            ->imageResizeTargetWidth('1920')
                            ->imageResizeTargetHeight('1080')
                            ->directory('sliders')
                            ->required()
                            ->helperText('الأبعاد الموصى بها: 1920x1080 بكسل'),
                        Forms\Components\TextInput::make('link')
                            ->label('رابط العرض')
                            ->required()
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://example.com')
                            ->helperText('الرابط الذي يتم الانتقال إليه عند الضغط'),
                        Forms\Components\Toggle::make('active')
                            ->label('نشط')
                            ->default(true)
                            ->helperText('العروض غير النشطة لن تظهر في الصفحة الرئيسية'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('الصورة')
                    ->height(80)
                    ->width(120),
                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->placeholder('بدون عنوان'),
                Tables\Columns\TextColumn::make('link')
                    ->label('الرابط')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function ($record) {
                        return $record->link;
                    }),
                Tables\Columns\IconColumn::make('active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label('الحالة')
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
                    ->label('تعديل'),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحدد'),
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
