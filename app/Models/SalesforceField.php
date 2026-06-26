<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesforceField extends Model
{
    protected $fillable = ['salesforce_object_id', 'label', 'api_name', 'type', 'referenced_to', 'picklist_values', 'is_insertable', 'is_updatable', 'is_readable'];

    protected $casts = ['picklist_values' => 'array'];

    public function salesforceObject()
    {
        return $this->belongsTo(SalesforceObject::class);
    }
}
