<?php

namespace App\Filament\Resources;

use App\Services\GoogleSheetsService;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Http\Response;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function getNavigationLabel(): string { return __('admin.resources.user.plural_label'); }

    public static function getModelLabel(): string { return __('admin.resources.user.label'); }

    public static function getPluralModelLabel(): string { return __('admin.resources.user.plural_label'); }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('admin.resources.user.label'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('admin.fields.name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('username')
                            ->label(__('admin.fields.username'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label(__('admin.fields.email'))
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                    ])->columns(3),

                Forms\Components\Section::make(__('admin.fields.password'))
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label(__('admin.fields.password'))
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->label(__('admin.fields.password_confirmation'))
                            ->password()
                            ->dehydrated(false)
                            ->required(fn (string $context): bool => $context === 'create'),
                    ])->columns(2),

                Forms\Components\Section::make(__('admin.fields.status'))
                    ->schema([
                        Forms\Components\DatePicker::make('birthday')
                            ->label(__('admin.fields.birthday'))
                            ->required()
                            ->maxDate(now()->subYears(5)),
                        Forms\Components\TextInput::make('phone')
                            ->label(__('admin.fields.phone'))
                            ->tel()
                            ->required()
                            ->maxLength(20),
                        Forms\Components\Textarea::make('address')
                            ->label(__('admin.fields.address'))
                            ->required()
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make(__('admin.fields.settings'))
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->label(__('admin.fields.image'))
                            ->image()
                            ->imageEditor()
                            ->directory('users')
                            ->nullable(),
                        Forms\Components\Toggle::make('active')
                            ->label(__('admin.fields.active'))
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('معلومات الطالب والعائلة')
                    ->schema([
                        Forms\Components\Select::make('parent_id')
                            ->label('ولي الأمر')
                            ->relationship('parent', 'name', fn (Builder $query) => $query->whereHas('roles', fn ($q) => $q->where('name', 'parent')))
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\Select::make('age_group')
                            ->label('الفئة العمرية')
                            ->options([
                                'nashieen' => 'ناشئين (7-10 سنوات)',
                                'yafeen' => 'يافعين (11-14 سنة)',
                                'fityan' => 'فتيان (15 سنة فما فوق)',
                            ])
                            ->nullable(),
                        Forms\Components\TextInput::make('school')
                            ->label('المدرسة')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('father_phone')
                            ->label('رقم هاتف الأب')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('father_job')
                            ->label('عمل الأب')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('mother_phone')
                            ->label('رقم هاتف الأم')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('mother_job')
                            ->label('عمل الأم')
                            ->maxLength(255),
                        Forms\Components\Toggle::make('wants_bus')
                            ->label('مشترك بالمواصلات')
                            ->default(false),
                        Forms\Components\TextInput::make('quran_pages')
                            ->label('مقدار حفظ القرآن (صفحة)')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Textarea::make('medical_notes')
                            ->label('الملاحظات الصحية')
                            ->columnSpanFull()
                            ->rows(3),
                        Forms\Components\Textarea::make('general_notes')
                            ->label('ملاحظات عامة')
                            ->columnSpanFull()
                            ->rows(3),
                    ])->columns(3),

                Forms\Components\Section::make('الأدوار والصلاحيات')
                    ->schema([
                        Forms\Components\CheckboxList::make('roles')
                            ->label('الأدوار')
                            ->relationship('roles', 'name')
                            ->searchable()
                            ->bulkToggleable()
                            ->options(function () {
                                $user = auth()->user();
                                if ($user->hasRole('admin')) {
                                    return \Spatie\Permission\Models\Role::pluck('name', 'id');
                                } elseif ($user->can('create users')) {
                                    // المعلم يمكنه فقط إنشاء طلاب
                                    return \Spatie\Permission\Models\Role::where('name', 'student')->pluck('name', 'id');
                                }
                                return [];
                            }),
                    ])
                    ->visible(fn () => auth()->user()->hasRole('admin') || auth()->user()->can('create users')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = auth()->user();
                if ($user->hasRole('admin')) {
                    return $query; // المدير يرى الجميع
                } elseif ($user->can('view users')) {
                    // المعلم يرى الطلاب فقط
                    return $query->whereHas('roles', function ($q) {
                        $q->where('name', 'student');
                    });
                }
                return $query->whereRaw('1 = 0'); // لا يرى أحد
            })
            ->headerActions([])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('subject_id')
                    ->label('Ders')
                    ->relationship('subjectsAsStudent', 'title')
                    ->searchable()
                    ->preload()
                    ->visible(fn () => auth()->user()->can('export students')),
                \Filament\Tables\Filters\SelectFilter::make('age_group')
                    ->label('الفئة العمرية')
                    ->options([
                        'nashieen' => 'ناشئين',
                        'yafeen' => 'يافعين',
                        'fityan' => 'فتيان',
                    ]),
                Tables\Filters\TernaryFilter::make('active')
                    ->label('نشط')
                    ->boolean()
                    ->trueLabel('نشط فقط')
                    ->falseLabel('غير نشط فقط'),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Başlangıç Tarihi'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Bitiş Tarihi'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                \App\Filament\Resources\UserResource\Actions\SendLoginWhatsAppAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Resim')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.fields.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('username')
                    ->label(__('admin.fields.username'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('admin.fields.email'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('الهاتف')
                    ->searchable(),
                Tables\Columns\TextColumn::make('age_group')
                    ->label('الفئة العمرية')
                    ->badge()
                    ->color(fn ($state): string => match ($state) {
                        'nashieen' => 'primary',
                        'yafeen' => 'warning',
                        'fityan' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'nashieen' => 'ناشئين',
                        'yafeen' => 'يافعين',
                        'fityan' => 'فتيان',
                        default => $state
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('ولي الأمر')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('active')
                    ->label(__('admin.fields.active'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ]);
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view users') || auth()->user()->hasRole('admin');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create users') || auth()->user()->hasRole('admin');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('edit users') || auth()->user()->hasRole('admin');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->can('delete users') || auth()->user()->hasRole('admin');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('view users') || auth()->user()->hasRole('admin');
    }
}
