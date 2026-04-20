<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesforceObject extends Model
{
    protected $fillable = ['api_name', 'label', 'is_creatable', 'is_updatable', 'is_deletable'];

    public function fields()
    {
        return $this->hasMany(SalesforceField::class);
    }
}
