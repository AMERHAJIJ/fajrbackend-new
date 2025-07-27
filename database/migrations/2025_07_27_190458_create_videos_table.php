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
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image')->nullable();
            $table->string('link');
            $table->unsignedBigInteger('visits')->default(0);
            $table->boolean('isRequired')->default(false);
            $table->boolean('showInHomePage')->default(false);
            $table->boolean('active')->default(true);
            $table->string('object_type'); // category or subject
            $table->unsignedBigInteger('object_id');
            $table->timestamps();

            // Optional: You can index polymorphic relation for performance
            $table->index(['object_type', 'object_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
