<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')
                  ->constrained('test_modules')
                  ->cascadeOnDelete();
            $table->string('test_case_id', 20);                // e.g. 'tc001', 'tc010'
            $table->jsonb('parameters')->default('{}');         // all input + expected values
            $table->text('notes')->nullable();
            $table->timestamps();

            // Each (module, test_case) pair must be unique
            $table->unique(['module_id', 'test_case_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_parameters');
    }
};
