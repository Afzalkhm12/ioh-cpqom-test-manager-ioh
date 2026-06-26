<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_specs', function (Blueprint $table) {
            $table->jsonb('api_config')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('test_specs', function (Blueprint $table) {
            $table->dropColumn('api_config');
        });
    }
};
