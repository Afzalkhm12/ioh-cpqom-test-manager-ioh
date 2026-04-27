<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_modules', function (Blueprint $table) {
            $table->dropColumn('spec_file');
            $table->foreignId('spec_id')
                  ->nullable()
                  ->after('description')
                  ->constrained('test_specs')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('test_modules', function (Blueprint $table) {
            $table->dropConstrainedForeignId('spec_id');
            $table->string('spec_file')->nullable()->after('description');
        });
    }
};
