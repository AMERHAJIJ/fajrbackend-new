<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherTaskTrackerResource\Pages;
use App\Models\TeacherTaskTracker;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TeacherTaskTrackerResource extends Resource
{
    protected static ?string $model = TeacherTaskTracker::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    public static function getNavigationLabel(): string { return __('admin.resources.teacher_task_tracker.plural_label'); }
    public static function getModelLabel(): string { return __('admin.resources.teacher_task_tracker.label'); }
    public static function getPluralModelLabel(): string { return __('admin.resources.teacher_task_tracker.plural_label'); }
    public static function getNavigationGroup(): ?string { return __('admin.navigation_group.education_management'); }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make(__('admin.fields.notes'))
                ->description(__('admin.resources.teacher_task_tracker.label'))
                ->schema([
                    Forms\Components\Placeholder::make('teacher_name')
                        ->label(__('admin.fields.teacher'))
                        ->content(fn ($record) => $record?->teacher?->name),
                    
                    Forms\Components\Placeholder::make('subject_title')
                        ->label(__('admin.fields.subject'))
                        ->content(fn ($record) => $record?->subject?->title),
                    
                    Forms\Components\Placeholder::make('date_display')
                        ->label(__('admin.fields.date'))
                        ->content(fn ($record) => $record?->date?->format('d/m/Y')),
                ])->columns(3),
            
            Forms\Components\Section::make(__('admin.fields.status'))
                ->schema([
                    Forms\Components\Placeholder::make('attendance_taken_display')
                        ->label(__('admin.fields.attendance_taken'))
                        ->content(fn ($record) => $record ? $record->getAttendanceStats()['text'] . " ({$record->getAttendanceStats()['percentage']}%)" : '-'),
                    
                    Forms\Components\Placeholder::make('recitation_recorded_display')
                        ->label(__('admin.fields.recitation_recorded'))
                        ->content(fn ($record) => $record ? $record->getRecitationStats()['text'] . " ({$record->getRecitationStats()['percentage']}%)" : '-'),
                    
                    Forms\Components\Placeholder::make('next_recitation_set_display')
                        ->label(__('admin.fields.next_recitation_set'))
                        ->content(fn ($record) => $record ? $record->getNextRecitationStats()['text'] . " ({$record->getNextRecitationStats()['percentage']}%)" : '-'),
                    
                    Forms\Components\Placeholder::make('homework_sent_display')
                        ->label(__('admin.fields.homework_sent'))
                        ->content(fn ($record) => $record ? $record->getHomeworkStats()['text'] : '-'),
                    
                    Forms\Components\Placeholder::make('whatsapp_sent_display')
                        ->label(__('admin.fields.whatsapp_sent'))
                        ->content(fn ($record) => $record ? $record->getWhatsappStats()['text'] : '-'),
                ])->columns(5),
            
            Forms\Components\Section::make(__('admin.fields.notes'))
                ->schema([
                    Forms\Components\Textarea::make('notes')
                        ->label(__('admin.fields.notes'))
                        ->columnSpanFull()
                        ->rows(3),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label(__('admin.fields.teacher'))
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('subject.title')
                    ->label(__('admin.fields.subject'))
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('date')
                    ->label(__('admin.fields.date'))
                    ->date('d/m/Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('completion_percentage')
                    ->label(__('admin.fields.completion_percentage'))
                    ->suffix('%')
                    ->badge()
                    ->color(fn ($state) => $state >= 80 ? 'success' : ($state >= 40 ? 'warning' : 'danger')),

                Tables\Columns\TextColumn::make('attendance_status')
                    ->label(__('admin.fields.attendance_taken'))
                    ->getStateUsing(fn ($record) => $record->getAttendanceStats()['text'])
                    ->badge()
                    ->color(fn ($record) => $record->getAttendanceStats()['percentage'] >= 100 ? 'success' : 'gray'),
                
                Tables\Columns\TextColumn::make('recitation_status')
                    ->label(__('admin.fields.recitation_recorded'))
                    ->getStateUsing(fn ($record) => $record->getRecitationStats()['text'])
                    ->badge()
                    ->color(fn ($record) => $record->getRecitationStats()['percentage'] >= 100 ? 'success' : 'gray'),
                
                Tables\Columns\TextColumn::make('next_recitation_status')
                    ->label(__('admin.fields.next_recitation_set'))
                    ->getStateUsing(fn ($record) => $record->getNextRecitationStats()['text'])
                    ->badge()
                    ->color(fn ($record) => $record->getNextRecitationStats()['percentage'] >= 100 ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('homework_status')
                    ->label(__('admin.fields.homework_sent'))
                    ->getStateUsing(fn ($record) => $record->getHomeworkStats()['text'])
                    ->badge()
                    ->color(fn ($record) => $record->getHomeworkStats()['completed'] > 0 ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('whatsapp_status')
                    ->label(__('admin.fields.whatsapp_sent'))
                    ->getStateUsing(fn ($record) => $record->getWhatsappStats()['text'])
                    ->badge()
                    ->color(fn ($record) => $record->getWhatsappStats()['completed'] > 0 ? 'success' : 'danger'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('teacher_id')
                    ->label(__('admin.fields.teacher'))
                    ->relationship('teacher', 'name'),
                Tables\Filters\SelectFilter::make('subject_id')
                    ->label(__('admin.fields.subject'))
                    ->relationship('subject', 'title'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()->label(__('admin.actions.add_notes')),
            ])
            ->defaultSort('date', 'desc');
    }

    // [شرح أكاديمي للمناقشة]:
    // هذه الدالة تمثل ميزة (عزل البيانات Data Scoping). 
    // وظيفتها التأكد من أن المعلم الذي يقوم بتسجيل الدخول لا يرى المهام والإحصائيات
    // الخاصة بزملائه المعلمين، بل يرى ما يخصه هو فقط. بينما المدير (Admin) 
    // يمتلك الصلاحية لرؤية لوحة الإنجازات لجميع المعلمين في المدرسة للتقييم.
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        if (Auth::check() && Auth::user()->roles->contains('name', 'teacher')) {
            $query->where('teacher_id', Auth::id());
        }
        return $query;
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
            'index' => Pages\ListTeacherTaskTrackers::route('/'),
            'view' => Pages\ViewTeacherTaskTracker::route('/{record}'),
            'edit' => Pages\EditTeacherTaskTracker::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return Auth::check() && Auth::user()->roles->contains('name', 'admin');
    }

    public static function canDelete($record): bool
    {
        return Auth::check() && Auth::user()->roles->contains('name', 'admin');
    }

    public static function canViewAny(): bool
    {
        if (!Auth::check()) {
            return false;
        }
        
        $user = Auth::user();
        
        // تظهر للمدير والأدمن فقط، وتختفي من الأستاذ والطالب
        return $user->hasAnyRole(['admin', 'director', 'manager', 'مدير']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }
}
