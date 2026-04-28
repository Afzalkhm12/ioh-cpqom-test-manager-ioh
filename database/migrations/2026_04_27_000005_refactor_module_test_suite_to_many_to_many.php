<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old single FK column
        Schema::table('modules', function (Blueprint $table) {
            $table->dropForeign(['test_module_id']);
            $table->dropColumn('test_module_id');
        });

        // Create the pivot table
        Schema::create('module_test_module', function (Blueprint $table) {
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->foreignId('test_module_id')->constrained('test_modules')->cascadeOnDelete();
            $table->primary(['module_id', 'test_module_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_test_module');

        Schema::table('modules', function (Blueprint $table) {
            $table->foreignId('test_module_id')
                  ->nullable()
                  ->constrained('test_modules')
                  ->nullOnDelete()
                  ->after('api_schema');
        });
    }
};
