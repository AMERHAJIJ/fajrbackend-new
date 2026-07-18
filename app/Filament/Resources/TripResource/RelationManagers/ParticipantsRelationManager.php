<?php

namespace App\Filament\Resources\TripResource\RelationManagers;

use App\Models\TripBus;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ParticipantsRelationManager extends RelationManager
{
    protected static string $relationship = 'participants';
    
    protected static ?string $title = 'الطلاب المشاركين';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('student_id')
                    ->label('Öğrenci')
                    ->options(User::whereHas('roles', fn($q) => $q->where('name', 'student'))->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('حالة المشاركة')
                    ->options([
                        'confirmed' => 'مؤكد',
                        'pending' => 'قيد الانتظار',
                        'not_participating' => 'غير مشارك',
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\Select::make('teacher_id')
                    ->label('المشرف/المعلم')
                    ->options(User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))->pluck('name', 'id'))
                    ->searchable(),
                Forms\Components\Select::make('trip_bus_id')
                    ->label('الباص')
                    ->options(fn ($livewire) => TripBus::where('trip_id', $livewire->ownerRecord->id)->pluck('name', 'id'))
                    ->searchable(),
                Forms\Components\Select::make('payment_status')
                    ->label('حالة الدفع')
                    ->options([
                        'unpaid' => 'غير مدفوع',
                        'partial' => 'دفع جزئي',
                        'paid' => 'مدفوع بالكامل',
                        'overdue' => 'متأخر',
                    ])
                    ->default('unpaid')
                    ->required(),
                Forms\Components\TextInput::make('paid_amount')
                    ->label('المبلغ المدفوع')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('student.name')
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Öğrenci')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('المشاركة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'confirmed' => 'success',
                        'pending' => 'warning',
                        'not_participating' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'confirmed' => 'مؤكد',
                        'pending' => 'قيد الانتظار',
                        'not_participating' => 'غير مشارك',
                    }),
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('المشرف')
                    ->placeholder('لم يتم التعيين'),
                Tables\Columns\TextColumn::make('bus.name')
                    ->label('الباص')
                    ->placeholder('لم يتم التعيين'),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('الدفع')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'partial' => 'warning',
                        'unpaid' => 'danger',
                        'overdue' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'paid' => 'مدفوع',
                        'partial' => 'جزئي',
                        'unpaid' => 'لم يدفع',
                        'overdue' => 'متأخر',
                    }),
                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('المدفوع')
                    ->money('USD'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('حالة المشاركة')
                    ->options([
                        'confirmed' => 'مؤكد',
                        'pending' => 'قيد الانتظار',
                        'not_participating' => 'غير مشارك',
                    ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('حالة الدفع')
                    ->options([
                        'unpaid' => 'غير مدفوع',
                        'partial' => 'دفع جزئي',
                        'paid' => 'مدفوع بالكامل',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة مشارك'),
            ])
            ->actions([
                Tables\Actions\Action::make('whatsapp')
                    ->label('واتساب')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->url(function ($record) {
                        $phone = ltrim($record->student->phone, '0');
                        $tripTitle = $record->trip->title;
                        $remaining = $record->remaining_amount;
                        
                        if ($record->payment_status !== 'paid') {
                            $message = "السلام عليكم، نود تذكيركم بخصوص رحلة ({$tripTitle})، المبلغ المتبقي هو {$remaining}. يرجى التسديد في أقرب وقت.";
                        } else {
                            $message = "السلام عليكم، بخصوص رحلة ({$tripTitle})، تم تأكيد مشاركة الطالب وجاهزيته. شكراً لكم.";
                        }
                        
                        return "https://wa.me/{$phone}?text=" . urlencode($message);
                    })
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('assignTeacher')
                        ->label('تعيين مشرف للمجموعة')
                        ->icon('heroicon-o-user-group')
                        ->form([
                            Forms\Components\Select::make('teacher_id')
                                ->label('المعلم المشرف')
                                ->options(User::whereHas('roles', fn($q) => $q->where('name', 'teacher'))->pluck('name', 'id'))
                                ->required(),
                        ])
                        ->action(function (Builder $query, array $data): void {
                            $query->update(['teacher_id' => $data['teacher_id']]);
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
