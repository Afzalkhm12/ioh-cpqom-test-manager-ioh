<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_modules', function (Blueprint $table) {
            $table->id();
            $table->string('module_key', 100)->unique();        // e.g. 'account_mgmt'
            $table->string('display_name');                     // e.g. 'Account Management'
            $table->integer('counter')->default(0);             // incremented each test run
            $table->foreignId('default_credential_id')
                  ->nullable()
                  ->constrained('salesforce_users')
                  ->nullOnDelete();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_modules');
    }
};
