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

    protected static ?string $navigationIcon = 'heroicon-o-arrow-right-circle';

    public static function getNavigationLabel(): string { return __('admin.resources.next_recitation.plural_label'); }
    
    public static function getModelLabel(): string { return __('admin.resources.next_recitation.label'); }
    
    public static function getPluralModelLabel(): string { return __('admin.resources.next_recitation.plural_label'); }
    
    public static function getNavigationGroup(): ?string { return __('admin.navigation_group.education_management'); }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('admin.resources.next_recitation.label'))
                    ->schema([
                        Forms\Components\Select::make('subject_id')
                            ->label('الحلقة')
                            ->options(function () {
                                $user = auth()->user();
                                if ($user->hasRole('teacher')) {
                                    return \App\Models\Subject::where('is_quran', true)
                                        ->whereHas('teachers', function ($q) use ($user) {
                                            $q->where('teacher_id', $user->id);
                                        })
                                        ->pluck('title', 'id');
                                }
                                return \App\Models\Subject::where('is_quran', true)->pluck('title', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('student_id', null)),

                        Forms\Components\Select::make('student_id')
                            ->label(__('admin.fields.student'))
                            ->options(function (callable $get) {
                                $subjectId = $get('subject_id');
                                if (!$subjectId) {
                                    return [];
                                }
                                return \App\Models\User::role('student')
                                    ->whereHas('subjectsAsStudent', function ($q) use ($subjectId) {
                                        $q->where('subject_id', $subjectId);
                                    })
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        Forms\Components\Hidden::make('teacher_id')
                            ->default(auth()->id()),
                        Forms\Components\Repeater::make('surahs')
                            ->label(__('admin.resources.surah.plural_label'))
                            ->relationship('surahs')
                            ->schema([
                                Forms\Components\Select::make('id')
                                    ->label(__('admin.fields.surah'))
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
                                    ->label(__('admin.fields.registration_type'))
                                    ->options([
                                        'ayah' => __('admin.options.ayah_range'),
                                        'page' => __('admin.options.page_range')
                                    ])
                                    ->default('ayah')
                                    ->live()
                                    ->required()
                                    ->columnSpan(1),
                                
                                // حقل مشروط للآيات
                                Forms\Components\Group::make([
                                    Forms\Components\TextInput::make('pivot.fromAyeh')
                                        ->label(__('admin.fields.from_ayah'))
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(286)
                                        ->default(1)
                                        ->visible(fn($get) => $get('pivot.type') === 'ayah')
                                        ->required(fn($get) => $get('pivot.type') === 'ayah'),
                                    Forms\Components\TextInput::make('pivot.toAyeh')
                                        ->label(__('admin.fields.to_ayah'))
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
                                        ->label(__('admin.fields.from_page'))
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(604)
                                        ->default(1)
                                        ->visible(fn($get) => $get('pivot.type') === 'page')
                                        ->required(fn($get) => $get('pivot.type') === 'page'),
                                    Forms\Components\TextInput::make('pivot.toPage')
                                        ->label(__('admin.fields.to_page'))
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
                            ->addActionLabel(__('admin.actions.add_another_surah'))
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
                    ->label('الطلب')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject.title')
                    ->label('الحلقة')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('surahs')
                    ->label(__('admin.resources.surah.plural_label'))
                    ->formatStateUsing(function ($record) {
                        return $record->surahs->map(function ($surah) {
                            if ($surah->pivot->type === 'ayah') {
                                return $surah->name . ' (' . __('admin.sections.from') . ' ' . __('admin.fields.ayah') . ' ' . $surah->pivot->fromAyeh . ' ' . __('admin.sections.to') . ' ' . $surah->pivot->toAyeh . ')';
                            } else {
                                return $surah->name . ' (' . __('admin.sections.from') . ' ' . __('admin.fields.page') . ' ' . $surah->pivot->fromPage . ' ' . __('admin.sections.to') . ' ' . $surah->pivot->toPage . ')';
                            }
                        })->implode('، ');
                    })
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.fields.created_at'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subject_id')
                    ->label('الحلقة')
                    ->options(function () {
                        $user = auth()->user();
                        if ($user->hasRole('teacher')) {
                            return \App\Models\Subject::where('is_quran', true)
                                ->whereHas('teachers', function ($q) use ($user) {
                                    $q->where('teacher_id', $user->id);
                                })
                                ->pluck('title', 'id');
                        }
                        return \App\Models\Subject::where('is_quran', true)->pluck('title', 'id');
                    })
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('surah')
                    ->label(__('admin.fields.surah'))
                    ->relationship('surahs', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('admin.fields.registration_type'))
                    ->options([
                        'ayah' => __('admin.options.ayahs'),
                        'page' => __('admin.options.pages')
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
                    ->label(__('admin.fields.student'))
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
                    ->label(__('admin.actions.edit')),
                Tables\Actions\DeleteAction::make()
                    ->label(__('admin.actions.delete')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label(__('admin.actions.delete_selected')),
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
