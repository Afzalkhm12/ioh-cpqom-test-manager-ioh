<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesforceField extends Model
{
    protected $fillable = ['salesforce_object_id', 'label', 'api_name', 'type', 'is_insertable', 'is_updatable', 'is_readable'];

    public function salesforceObject()
    {
        return $this->belongsTo(SalesforceObject::class);
    }
}
