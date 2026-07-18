<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LiveSessionResource\Pages;
use App\Models\LiveSession;
use App\Models\Subject;
use App\Models\User;
use App\Services\GoogleMeetService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class LiveSessionResource extends Resource
{
    protected static ?string $model = LiveSession::class;

    protected static ?string $navigationIcon = 'heroicon-o-video-camera';
    public static function getNavigationLabel(): string { return __('admin.resources.live_session.plural_label'); }
    public static function getModelLabel(): string { return __('admin.resources.live_session.label'); }
    public static function getPluralModelLabel(): string { return __('admin.resources.live_session.plural_label'); }
    public static function getNavigationGroup(): ?string { return __('admin.navigation_group.education_management'); }
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('admin.resources.live_session.label'))
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label(__('admin.fields.title'))
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Textarea::make('description')
                        ->label(__('admin.fields.description'))
                        ->rows(3)
                        ->nullable(),
                ])->columns(1),

            Forms\Components\Section::make(__('admin.resources.live_session.assignment'))
                ->schema([
                    Forms\Components\Select::make('subject_id')
                        ->label(__('admin.fields.subject'))
                        ->options(function () {
                            $user = auth()->user();
                            if ($user->hasRole('teacher')) {
                                return $user->subjectsAsTeacher()->pluck('title', 'id');
                            }
                            return Subject::pluck('title', 'id');
                        })
                        ->required()
                        ->searchable()
                        ->preload(),

                    Forms\Components\Select::make('teacher_id')
                        ->label(__('admin.fields.teacher'))
                        ->options(function () {
                            $user = auth()->user();
                            if ($user->hasRole('teacher')) {
                                return [$user->id => $user->name];
                            }
                            return User::role('teacher')->pluck('name', 'id');
                        })
                        ->default(fn () => auth()->user()->hasRole('teacher') ? auth()->id() : null)
                        ->disabled(fn () => auth()->user()->hasRole('teacher'))
                        ->required()
                        ->searchable()
                        ->preload(),
                ])->columns(2),

            Forms\Components\Section::make(__('admin.resources.live_session.timing'))
                ->schema([
                    Forms\Components\DateTimePicker::make('start_time')
                        ->label(__('admin.fields.start_at'))
                        ->required()
                        ->native(false)
                        ->minutesStep(15)
                        ->minDate(now()),

                    Forms\Components\DateTimePicker::make('end_time')
                        ->label(__('admin.fields.end_at'))
                        ->required()
                        ->native(false)
                        ->minutesStep(15)
                        ->after('start_time'),
                ])->columns(2),

            Forms\Components\Section::make(__('admin.resources.live_session.settings'))
                ->schema([
                    Forms\Components\Toggle::make('active')
                        ->label(__('admin.fields.active'))
                        ->default(true),

                    Forms\Components\TextInput::make('meet_link')
                        ->label(__('admin.fields.link'))
                        ->url()
                        ->nullable()
                        ->disabled()
                        ->helperText(__('admin.fields.link_helper')),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('admin.fields.title'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject.title')
                    ->label(__('admin.fields.subject'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('teacher.name')
                    ->label(__('admin.fields.teacher'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_time')
                    ->label(__('admin.fields.start_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_time')
                    ->label(__('admin.fields.end_at'))
                    ->dateTime('H:i')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('admin.fields.status'))
                    ->getStateUsing(fn ($record) => $record->status)
                    ->colors([
                        'success' => 'upcoming',
                        'danger'  => 'live',
                        'gray'    => 'ended',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'upcoming' => '🟢 قادمة',
                        'live'     => '🔴 مباشرة الآن',
                        'ended'    => '⚫ منتهية',
                        default    => $state,
                    }),

                Tables\Columns\IconColumn::make('meet_link')
                    ->label('Meet')
                    ->boolean()
                    ->trueIcon('heroicon-o-video-camera')
                    ->falseIcon('heroicon-o-x-circle'),

                Tables\Columns\IconColumn::make('active')
                    ->label('نشط')
                    ->boolean(),
            ])
            ->defaultSort('start_time', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('subject_id')
                    ->label('Ders')
                    ->relationship('subject', 'title')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('active')
                    ->label('نشط')
                    ->trueLabel('النشطة فقط')
                    ->falseLabel('غير النشطة'),
            ])
            ->actions([
                Tables\Actions\Action::make('join')
                    ->label('انضم')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('success')
                    ->url(fn ($record) => $record->meet_link)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => !empty($record->meet_link)),

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->after(function ($record) {
                        if ($record->google_event_id) {
                            try {
                                app(GoogleMeetService::class)->deleteMeeting($record->google_event_id);
                            } catch (\Exception $e) {}
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Filter sessions based on role
     */
    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery();

        if ($user->hasRole('teacher')) {
            $query->where('teacher_id', $user->id);
        }

        if ($user->hasRole('student')) {
            $query->whereHas('subject.studentSubjects', function ($q) use ($user) {
                $q->where('student_id', $user->id);
            })->where('active', true);
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLiveSessions::route('/'),
            'create' => Pages\CreateLiveSession::route('/create'),
            'edit'   => Pages\EditLiveSession::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view live_sessions');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create live_sessions');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('edit live_sessions');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->can('delete live_sessions');
    }
}
