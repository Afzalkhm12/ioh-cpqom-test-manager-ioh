<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RuntimeState extends Model
{
    protected $table = 'runtime_state'; // prevent auto-pluralizing to 'runtime_states'

    public $timestamps = false;

    protected $fillable = [
        'state_key',
        'state_value',
        'description',
        'last_updated_at',
    ];

    protected $casts = [
        'last_updated_at' => 'datetime',
    ];

    /**
     * Upsert a state value in one call.
     */
    public static function setValue(string $key, string $value): self
    {
        return static::updateOrCreate(
            ['state_key' => $key],
            ['state_value' => $value, 'last_updated_at' => now()]
        );
    }

    /**
     * Get a state value by key, with optional default.
     */
    public static function getValue(string $key, ?string $default = null): ?string
    {
        return static::where('state_key', $key)->value('state_value') ?? $default;
    }
}
