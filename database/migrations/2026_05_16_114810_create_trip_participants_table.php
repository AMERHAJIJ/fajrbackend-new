<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trip_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('trip_bus_id')->nullable()->constrained()->nullOnDelete();
            
            $table->enum('status', ['confirmed', 'pending', 'not_participating'])->default('pending');
            
            $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'overdue'])->default('unpaid');
            $table->decimal('paid_amount', 10, 2)->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_participants');
    }
};
