<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesforceUser extends Model
{
    protected $fillable = ['label', 'username', 'refresh_token', 'access_token'];

    protected $casts = [
        'refresh_token' => 'encrypted',
        'access_token' => 'encrypted',
    ];
}
