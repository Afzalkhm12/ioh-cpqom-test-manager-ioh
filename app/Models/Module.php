<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = ['name', 'description', 'api_schema'];

    protected $casts = [
        'api_schema' => 'array',
    ];

    public function testCases()
    {
        return $this->hasMany(TestCase::class);
    }

    public function testModules()
    {
        return $this->belongsToMany(TestModule::class, 'module_test_module');
    }
}
