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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('school')->nullable();
            $table->string('father_job')->nullable();
            $table->string('mother_phone')->nullable();
            $table->string('age_group')->nullable(); // nashieen, yafeen, fityan
            $table->text('medical_notes')->nullable();
            $table->text('general_notes')->nullable();
            $table->boolean('wants_bus')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn([
                'parent_id',
                'school',
                'father_job',
                'mother_phone',
                'age_group',
                'medical_notes',
                'general_notes',
                'wants_bus'
            ]);
        });
    }
};
