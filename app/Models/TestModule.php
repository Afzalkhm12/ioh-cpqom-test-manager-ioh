<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestModule extends Model
{
    protected $fillable = [
        'module_key',
        'display_name',
        'category',
        'salesforce_module',
        'counter',
        'default_credential_id',
        'description',
        'spec_id',
    ];

    protected $casts = [
        'counter' => 'integer',
    ];

    public function testParameters(): HasMany
    {
        return $this->hasMany(TestParameter::class, 'module_id');
    }

    public function defaultCredential(): BelongsTo
    {
        return $this->belongsTo(SalesforceUser::class, 'default_credential_id');
    }

    public function spec(): BelongsTo
    {
        return $this->belongsTo(TestSpec::class, 'spec_id');
    }

    /**
     * Atomically increment the counter and return the new value.
     */
    public function incrementCounter(): int
    {
        $this->increment('counter');
        return $this->counter;
    }
}
