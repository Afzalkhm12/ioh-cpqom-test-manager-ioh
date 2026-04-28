<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->foreignId('test_module_id')
                  ->nullable()
                  ->constrained('test_modules')
                  ->nullOnDelete()
                  ->after('api_schema');
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\TestModule::class, 'test_module_id');
            $table->dropColumn('test_module_id');
        });
    }
};
