<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestRun extends Model
{
    protected $fillable = ['test_case_id', 'executed_by', 'status', 'logs', 'result_payload', 'executed_at'];

    protected $casts = [
        'result_payload' => 'array',
        'executed_at' => 'datetime',
    ];

    public function testCase()
    {
        return $this->belongsTo(TestCase::class);
    }

    public function executor()
    {
        return $this->belongsTo(User::class, 'executed_by');
    }
}
