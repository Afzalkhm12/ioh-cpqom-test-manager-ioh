<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salesforce_fields', function (Blueprint $table) {
            $table->string('referenced_to')->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('salesforce_fields', function (Blueprint $table) {
            $table->dropColumn('referenced_to');
        });
    }
};
