<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: add column as nullable so existing rows are not rejected
        Schema::table('test_parameters', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('module_id')->constrained('users')->cascadeOnDelete();
        });

        // Step 2: backfill existing rows to user 1 (first admin)
        DB::statement('UPDATE test_parameters SET user_id = 1 WHERE user_id IS NULL');

        // Step 3: make the column NOT NULL and update the unique index
        Schema::table('test_parameters', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
            $table->dropUnique(['module_id', 'test_case_id']);
            $table->unique(['module_id', 'test_case_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('test_parameters', function (Blueprint $table) {
            $table->dropUnique(['module_id', 'test_case_id', 'user_id']);
            $table->unique(['module_id', 'test_case_id']);
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
