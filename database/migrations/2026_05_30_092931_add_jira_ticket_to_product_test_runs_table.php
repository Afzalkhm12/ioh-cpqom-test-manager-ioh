<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_test_runs', function (Blueprint $table) {
            $table->string('jira_ticket')->nullable()->after('log');
        });
    }

    public function down(): void
    {
        Schema::table('product_test_runs', function (Blueprint $table) {
            $table->dropColumn('jira_ticket');
        });
    }
};
