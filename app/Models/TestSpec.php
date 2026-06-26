<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestSpec extends Model
{
    protected $fillable = [
        'display_name',
        'runner_key',
        'file_path',
        'description',
        'test_type',
        'api_config',
    ];

    protected $casts = [
        'api_config' => 'array',
    ];

    public function testModules(): HasMany
    {
        return $this->hasMany(TestModule::class, 'spec_id');
    }
}
