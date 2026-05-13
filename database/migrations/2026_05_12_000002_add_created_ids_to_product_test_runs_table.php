<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_test_runs', function (Blueprint $table) {
            $table->json('created_ids')->nullable()->after('runner_response');
            // null = not yet validated, 'passed' = tester marked OK, 'not_passed' = tester marked NG
            $table->string('validation_status')->nullable()->after('created_ids');
        });
    }

    public function down(): void
    {
        Schema::table('product_test_runs', function (Blueprint $table) {
            $table->dropColumn(['created_ids', 'validation_status']);
        });
    }
};
