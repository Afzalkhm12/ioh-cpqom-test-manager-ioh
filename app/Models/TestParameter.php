<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestParameter extends Model
{
    protected $fillable = [
        'module_id',
        'test_case_id',
        'parameters',
        'notes',
    ];

    protected $casts = [
        'parameters' => 'array',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(TestModule::class, 'module_id');
    }
}
