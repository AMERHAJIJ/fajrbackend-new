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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->date('date')->nullable();
            $table->string('location')->nullable();
            $table->time('departure_time')->nullable();
            $table->time('return_time')->nullable();
            $table->string('image')->nullable();
            $table->integer('capacity')->nullable();
            
            $table->enum('status', ['upcoming', 'active', 'finished'])->default('upcoming');
            
            $table->decimal('bus_cost', 10, 2)->nullable()->default(0);
            $table->decimal('food_cost', 10, 2)->nullable()->default(0);
            $table->decimal('entry_cost', 10, 2)->nullable()->default(0);
            $table->decimal('additional_cost', 10, 2)->nullable()->default(0);
            $table->decimal('other_expenses', 10, 2)->nullable()->default(0);
            $table->decimal('cost_per_student', 10, 2)->nullable()->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
