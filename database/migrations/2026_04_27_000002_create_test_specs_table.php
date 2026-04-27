<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_specs', function (Blueprint $table) {
            $table->id();
            $table->string('display_name');
            $table->string('runner_key', 100)->unique(); // key sent to automation runner
            $table->string('file_path');                 // e.g. tests/non-ida/01-account-mgmt.spec.js
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_specs');
    }
};
