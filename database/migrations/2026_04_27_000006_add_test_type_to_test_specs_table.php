<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_specs', function (Blueprint $table) {
            $table->string('test_type', 20)->default('ui')->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('test_specs', function (Blueprint $table) {
            $table->dropColumn('test_type');
        });
    }
};
