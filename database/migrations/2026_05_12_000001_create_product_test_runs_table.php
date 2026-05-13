<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_test_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_test_suite_id')->constrained('product_test_suites')->cascadeOnDelete();
            $table->foreignId('test_module_id')->constrained('test_modules')->cascadeOnDelete();
            $table->string('status')->default('running'); // running, success, error, aborted
            $table->text('log')->nullable();
            $table->json('runner_response')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_test_runs');
    }
};
