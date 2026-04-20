<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestCase extends Model
{
    protected $fillable = ['module_id', 'title', 'description', 'type', 'configuration'];

    protected $casts = [
        'configuration' => 'array',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function testRuns()
    {
        return $this->hasMany(TestRun::class);
    }
}
