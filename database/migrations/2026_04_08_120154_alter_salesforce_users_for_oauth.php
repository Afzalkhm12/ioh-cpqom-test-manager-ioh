<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('salesforce_users', function (Blueprint $table) {
            $table->dropColumn(['password', 'security_token']);
            $table->text('refresh_token')->nullable()->after('access_token');
        });
    }

    public function down(): void
    {
        Schema::table('salesforce_users', function (Blueprint $table) {
            $table->text('password')->nullable();
            $table->text('security_token')->nullable();
            $table->dropColumn('refresh_token');
        });
    }
};
