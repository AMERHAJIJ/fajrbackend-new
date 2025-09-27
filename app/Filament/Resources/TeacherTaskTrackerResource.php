<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherTaskTrackerResource\Pages;
use App\Models\TeacherTaskTracker;
use App\Models\User;
use App\Models\Subject;
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
    protected static ?string $navigationLabel = 'تتبع مهام المعلمين';
    protected static ?string $modelLabel = 'تتبع مهام المعلم';
    protected static ?string $pluralModelLabel = 'تتبع مهام المعلمين';
    protected static ?string $navigationGroup = 'إدارة التعليم';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('معلومات التتبع')
                ->schema([
                    Forms\Components\Select::make('teacher_id')
                        ->label('المعلم')
                        ->relationship('teacher', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->options(function () {
                            return User::role('teacher')->pluck('name', 'id');
                        }),
                    
                    Forms\Components\Select::make('subject_id')
                        ->label('المادة')
                        ->relationship('subject', 'title')
                        ->searchable()
                        ->preload()
                        ->required(),
                    
                    Forms\Components\DatePicker::make('date')
                        ->label('التاريخ')
                        ->required()
                        ->default(now()),
                ])->columns(3),
            
            Forms\Components\Section::make('مهام المعلم')
                ->schema([
                    Forms\Components\Toggle::make('attendance_taken')
                        ->label('أخذ الحضور')
                        ->helperText('هل أخذ المعلم الحضور لهذا اليوم؟')
                        ->onColor('success')
                        ->offColor('danger'),
                    
                    Forms\Components\Toggle::make('recitation_recorded')
                        ->label('تسجيل التلاوة')
                        ->helperText('هل سجل المعلم تسجيلات التلاوة؟')
                        ->onColor('success')
                        ->offColor('danger'),
                    
                    Forms\Components\Toggle::make('next_recitation_set')
                        ->label('التلاوة التالية')
                        ->helperText('هل سجل المعلم التلاوة التالية؟')
                        ->onColor('success')
                        ->offColor('danger'),
                    
                    Forms\Components\Toggle::make('whatsapp_sent')
                        ->label('إرسال الواتساب')
                        ->helperText('هل أرسل المعلم على الواتساب؟')
                        ->onColor('success')
                        ->offColor('danger'),
                    
                    Forms\Components\Toggle::make('homework_sent')
                        ->label('إرسال الواجبات')
                        ->helperText('هل أرسل المعلم الواجبات؟')
                        ->onColor('success')
                        ->offColor('danger'),
                ])->columns(2),
            
            Forms\Components\Section::make('ملاحظات إضافية')
                ->schema([
                    Forms\Components\Textarea::make('notes')
                        ->label('ملاحظات')
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
                    ->label('المعلم')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('subject.title')
                    ->label('المادة')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('d/m/Y')
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('attendance_taken')
                    ->label('الحضور')
                    ->boolean()
                    ->trueIcon('heroicon-o-check')
                    ->falseIcon('heroicon-o-x-mark'),
                
                Tables\Columns\IconColumn::make('recitation_recorded')
                    ->label('التلاوة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check')
                    ->falseIcon('heroicon-o-x-mark'),
                
                Tables\Columns\IconColumn::make('next_recitation_set')
                    ->label('التلاوة التالية')
                    ->boolean()
                    ->trueIcon('heroicon-o-check')
                    ->falseIcon('heroicon-o-x-mark'),
                
                Tables\Columns\IconColumn::make('whatsapp_sent')
                    ->label('الواتساب')
                    ->boolean()
                    ->trueIcon('heroicon-o-check')
                    ->falseIcon('heroicon-o-x-mark'),
                
                Tables\Columns\IconColumn::make('homework_sent')
                    ->label('الواجبات')
                    ->boolean()
                    ->trueIcon('heroicon-o-check')
                    ->falseIcon('heroicon-o-x-mark'),
                
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('teacher_id')
                    ->label('المعلم')
                    ->relationship('teacher', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('subject_id')
                    ->label('المادة')
                    ->relationship('subject', 'title')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from_date')
                            ->label('من تاريخ'),
                        Forms\Components\DatePicker::make('to_date')
                            ->label('إلى تاريخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['to_date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
                
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
            ->defaultSort('date', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // للمعلمين، عرض مهامهم فقط
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

    public static function canViewAny(): bool
    {
        if (!Auth::check()) {
            return false;
        }
        
        $user = Auth::user();
        return $user->roles->contains('name', 'admin'); // فقط للإدمن
    }

    public static function canCreate(): bool
    {
        return false; // لا يمكن إنشاء سجلات يدوياً
    }

    public static function canEdit($record): bool
    {
        return Auth::check() && Auth::user()->roles->contains('name', 'admin');
    }

    public static function canDelete($record): bool
    {
        return Auth::check() && Auth::user()->roles->contains('name', 'admin');
    }
}
