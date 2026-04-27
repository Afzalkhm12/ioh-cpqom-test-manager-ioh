<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('runtime_state', function (Blueprint $table) {
            $table->id();
            $table->string('state_key', 100)->unique();         // e.g. 'opportunityId'
            $table->text('state_value')->nullable();             // the current dynamic value
            $table->text('description')->nullable();             // human-readable explanation
            $table->timestamp('last_updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        // Add GIN index on test_parameters.parameters for fast JSONB lookups
        // (Must be raw SQL — Blueprint doesn't support GIN)
        \Illuminate\Support\Facades\DB::statement(
            'CREATE INDEX idx_test_parameters_params_gin ON test_parameters USING GIN (parameters)'
        );
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement(
            'DROP INDEX IF EXISTS idx_test_parameters_params_gin'
        );
        Schema::dropIfExists('runtime_state');
    }
};
