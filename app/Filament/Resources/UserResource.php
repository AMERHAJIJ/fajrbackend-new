<?php

namespace App\Filament\Resources;

use App\Exports\StudentReportExport;
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
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Notification;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'المستخدمين';

    protected static ?string $modelLabel = 'مستخدم';

    protected static ?string $pluralModelLabel = 'المستخدمين';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('المعلومات الأساسية')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('username')
                            ->label('اسم المستخدم')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                    ])->columns(3),

                Forms\Components\Section::make('كلمة المرور')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('كلمة المرور')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('تأكيد كلمة المرور')
                            ->password()
                            ->dehydrated(false)
                            ->required(fn (string $context): bool => $context === 'create'),
                    ])->columns(2),

                Forms\Components\Section::make('المعلومات الشخصية')
                    ->schema([
                        Forms\Components\DatePicker::make('birthday')
                            ->label('تاريخ الميلاد')
                            ->required()
                            ->maxDate(now()->subYears(5)),
                        Forms\Components\TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->required()
                            ->maxLength(20),
                        Forms\Components\Textarea::make('address')
                            ->label('العنوان')
                            ->required()
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('الإعدادات')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->label('الصورة الشخصية')
                            ->image()
                            ->imageEditor()
                            ->directory('users')
                            ->nullable(),
                        Forms\Components\Toggle::make('active')
                            ->label('نشط')
                            ->default(true),
                    ])->columns(2),

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
            ->headerActions([
                Tables\Actions\Action::make('export_students')
                    ->label('تصدير تقرير الطلاب')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function ($livewire) {
                        $date = now()->format('Y-m-d');
                        $subjectId = $livewire->tableFilters['subject_id']['value'] ?? null;
                        
                        // Validate if subject is selected (if needed)
                        if (!$subjectId) {
                            Notification::make()
                                ->title('خطأ')
                                ->body('يجب اختيار مادة أولاً')
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        // Create and return the Excel file for download
                        $export = new StudentReportExport($subjectId, $date);
                        return $export->download();
                    })
                    ->visible(fn () => auth()->user()->can('export students')),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('subject_id')
                    ->label('المادة')
                    ->relationship('subjectsAsStudent', 'title')
                    ->searchable()
                    ->preload()
                    ->visible(fn () => auth()->user()->can('export students')),
                Tables\Filters\TernaryFilter::make('active')
                    ->label('نشط')
                    ->boolean()
                    ->trueLabel('نشط فقط')
                    ->falseLabel('غير نشط فقط'),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('من تاريخ'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('إلى تاريخ'),
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
                    ->label('الصورة')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('username')
                    ->label('اسم المستخدم')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('الهاتف')
                    ->searchable(),
                Tables\Columns\IconColumn::make('active')
                    ->label('نشط')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
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
