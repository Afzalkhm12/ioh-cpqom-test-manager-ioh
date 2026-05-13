<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_test_runs', function (Blueprint $table) {
            $table->text('finding')->nullable()->after('validation_status');
            $table->json('evidence_images')->nullable()->after('finding');
        });
    }

    public function down(): void
    {
        Schema::table('product_test_runs', function (Blueprint $table) {
            $table->dropColumn(['finding', 'evidence_images']);
        });
    }
};
