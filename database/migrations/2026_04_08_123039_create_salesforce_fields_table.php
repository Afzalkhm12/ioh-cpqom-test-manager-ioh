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
        Schema::create('salesforce_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salesforce_object_id')->constrained()->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->string('api_name');
            $table->string('type')->nullable();
            $table->boolean('is_insertable')->default(false);
            $table->boolean('is_updatable')->default(false);
            $table->boolean('is_readable')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salesforce_fields');
    }
};
