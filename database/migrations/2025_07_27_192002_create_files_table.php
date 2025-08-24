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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
$table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('link');
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('visits')->default(0);
            $table->boolean('showInHomePage')->default(false);
            $table->string('object_type'); // category or video
            $table->unsignedBigInteger('object_id');
            $table->timestamps();

            // تحسين الأداء لعلاقات polymorphic
            $table->index(['object_type', 'object_id']);        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
