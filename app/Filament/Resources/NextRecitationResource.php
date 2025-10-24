<?php
namespace App\Filament\Resources;

use App\Filament\Resources\NextRecitationResource\Pages;
use App\Filament\Resources\NextRecitationResource\RelationManagers;
use App\Models\NextRecitation;
use App\Models\Surah;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NextRecitationResource extends Resource
{
    protected static ?string $model = NextRecitation::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'التلاوة التالية';

    protected static ?string $modelLabel = 'تلاوة تالية';

    protected static ?string $pluralModelLabel = 'التلاوة التالية';

    protected static ?string $navigationGroup = 'إدارة التلاوة والحفظ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات التلاوة التالية')
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->label('الطالب')
                            ->relationship(
                                name: 'student',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (Builder $query) {
                                    // Get the current authenticated user
                                    $user = auth()->user();
                                    
                                    // If admin, show all students
                                    if ($user->hasRole('admin')) {
                                        return $query->role('student');
                                    }
                                    
                                    // If teacher, show only their students
                                    if ($user->hasRole('teacher')) {
                                        return $query->whereHas('subjectsAsStudent', function($q) use ($user) {
                                            $q->whereHas('teachers', function($q) use ($user) {
                                                $q->where('users.id', $user->id);
                                            });
                                        })->role('student');
                                    }
                                    
                                    return $query->where('id', 0); // Empty result if not admin or teacher
                                }
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('اسم الطالب')
                                    ->required(),
                                Forms\Components\TextInput::make('username')
                                    ->label('اسم المستخدم')
                                    ->required(),
                                Forms\Components\TextInput::make('email')
                                    ->label('البريد الإلكتروني')
                                    ->email()
                                    ->required(),
                            ]),
                        Forms\Components\Repeater::make('surahs')
                            ->label('السور')
                            ->relationship('surahs')
                            ->schema([
                                Forms\Components\Select::make('id')
                                    ->label('السورة')
                                    ->relationship('surah', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->optionsLimit(114)
                                    ->required()
                                    ->getOptionLabelFromRecordUsing(fn (Surah $record) => $record->id . ' - ' . $record->name)
                                    ->options(
                                        Surah::orderBy('id')
                                            ->pluck('name', 'id')
                                            ->mapWithKeys(fn ($name, $id) => [$id => $id . ' - ' . $name])
                                    ),
                                
                                Forms\Components\Select::make('pivot.type')
                                    ->label('نوع التسجيل')
                                    ->options([
                                        'ayah' => 'من آية إلى آية',
                                        'page' => 'من صفحة إلى صفحة'
                                    ])
                                    ->default('ayah')
                                    ->live()
                                    ->required()
                                    ->columnSpan(1),
                                
                                // حقل مشروط للآيات
                                Forms\Components\Group::make([
                                    Forms\Components\TextInput::make('pivot.fromAyeh')
                                        ->label('من آية')
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(286)
                                        ->default(1)
                                        ->visible(fn($get) => $get('pivot.type') === 'ayah')
                                        ->required(fn($get) => $get('pivot.type') === 'ayah'),
                                    Forms\Components\TextInput::make('pivot.toAyeh')
                                        ->label('إلى آية')
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(286)
                                        ->default(1)
                                        ->visible(fn($get) => $get('pivot.type') === 'ayah')
                                        ->required(fn($get) => $get('pivot.type') === 'ayah')
                                        ->gt('pivot.fromAyeh'),
                                ])->columns(2),
                                
                                // حقل مشروط للصفحات
                                Forms\Components\Group::make([
                                    Forms\Components\TextInput::make('pivot.fromPage')
                                        ->label('من صفحة')
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(604)
                                        ->default(1)
                                        ->visible(fn($get) => $get('pivot.type') === 'page')
                                        ->required(fn($get) => $get('pivot.type') === 'page'),
                                    Forms\Components\TextInput::make('pivot.toPage')
                                        ->label('إلى صفحة')
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(604)
                                        ->default(1)
                                        ->visible(fn($get) => $get('pivot.type') === 'page')
                                        ->required(fn($get) => $get('pivot.type') === 'page')
                                        ->gt('pivot.fromPage'),
                                ])->columns(2),
                            ])
                            ->columns(1)
                            ->defaultItems(1)
                            ->minItems(1)
                            ->addActionLabel('إضافة سورة أخرى')
                            ->reorderable()
                            ->columnSpanFull()
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                $pivotData = [
                                    'type' => $data['pivot']['type'],
                                ];
                                
                                if ($data['pivot']['type'] === 'ayah') {
                                    $pivotData['fromAyeh'] = $data['pivot']['fromAyeh'];
                                    $pivotData['toAyeh'] = $data['pivot']['toAyeh'];
                                } else {
                                    $pivotData['fromPage'] = $data['pivot']['fromPage'];
                                    $pivotData['toPage'] = $data['pivot']['toPage'];
                                }
                                
                                return $pivotData;
                            })
                            ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                                $pivotData = [
                                    'type' => $data['pivot']['type'],
                                ];
                                
                                if ($data['pivot']['type'] === 'ayah') {
                                    $pivotData['fromAyeh'] = $data['pivot']['fromAyeh'];
                                    $pivotData['toAyeh'] = $data['pivot']['toAyeh'];
                                } else {
                                    $pivotData['fromPage'] = $data['pivot']['fromPage'];
                                    $pivotData['toPage'] = $data['pivot']['toPage'];
                                }
                                
                                return $pivotData;
                            })
                            ->saveRelationshipsUsing(function (NextRecitation $record, array $state) {
                                $record->surahs()->sync(
                                    collect($state)
                                        ->mapWithKeys(function ($item) {
                                            $pivotData = [
                                                'type' => $item['pivot']['type'],
                                            ];
                                            
                                            if ($item['pivot']['type'] === 'ayah') {
                                                $pivotData['fromAyeh'] = $item['pivot']['fromAyeh'];
                                                $pivotData['toAyeh'] = $item['pivot']['toAyeh'];
                                            } else {
                                                $pivotData['fromPage'] = $item['pivot']['fromPage'];
                                                $pivotData['toPage'] = $item['pivot']['toPage'];
                                            }
                                            
                                            return [$item['id'] => $pivotData];
                                        })
                                );
                            }),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label('الطالب')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('surahs')
                    ->label('السور')
                    ->formatStateUsing(function ($record) {
                        return $record->surahs->map(function ($surah) {
                            if ($surah->pivot->type === 'ayah') {
                                return $surah->name . ' (من آية ' . $surah->pivot->fromAyeh . ' إلى ' . $surah->pivot->toAyeh . ')';
                            } else {
                                return $surah->name . ' (من صفحة ' . $surah->pivot->fromPage . ' إلى ' . $surah->pivot->toPage . ')';
                            }
                        })->implode('، ');
                    })
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('surah')
                    ->label('السورة')
                    ->relationship('surahs', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('type')
                    ->label('نوع التسجيل')
                    ->options([
                        'ayah' => 'آيات',
                        'page' => 'صفحات'
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!$data['value']) {
                            return $query;
                        }
                        return $query->whereHas('surahs', function ($q) use ($data) {
                            $q->where('next_recitation_surah.type', $data['value']);
                        });
                    }),
                Tables\Filters\SelectFilter::make('student_id')
                    ->label('الطالب')
                    ->relationship(
                        name: 'student',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query) {
                            $user = auth()->user();
                            
                            if ($user->hasRole('admin')) {
                                return $query->role('student');
                            }
                            
                            if ($user->hasRole('teacher')) {
                                return $query->whereHas('subjectsAsStudent', function($q) use ($user) {
                                    $q->whereHas('teachers', function($q) use ($user) {
                                        $q->where('users.id', $user->id);
                                    });
                                })->role('student');
                            }
                            
                            return $query->where('id', 0);
                        }
                    )
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
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
            'index' => Pages\ListNextRecitations::route('/'),
            'create' => Pages\CreateNextRecitation::route('/create'),
            'edit' => Pages\EditNextRecitation::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        $user = auth()->user();
        
        // If user is admin, show all records
        if ($user->hasRole('admin')) {
            return $query;
        }
        
        // If user is teacher, show only their students' records
        if ($user->hasRole('teacher')) {
            return $query->whereHas('student', function($q) use ($user) {
                $q->whereHas('subjectsAsStudent', function($q) use ($user) {
                    $q->whereHas('teachers', function($q) use ($user) {
                        $q->where('users.id', $user->id);
                    });
                });
            });
        }
        
        // For other roles, show only their own records
        return $query->where('student_id', $user->id);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create next_recitations');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('edit next_recitations');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->can('delete next_recitations');
    }

    public static function canView($record): bool
    {
        return auth()->user()->can('view next_recitations');
    }
}
