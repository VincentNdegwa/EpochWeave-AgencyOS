<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CustomFieldValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'custom_field_definition_id',
        'valueable_type',
        'valueable_id',
        'value_text',
        'value_number',
        'value_boolean',
        'value_date',
        'value_json',
    ];

    protected $casts = [
        'value_boolean' => 'boolean',
        'value_date' => 'date',
        'value_json' => 'array',
        'value_number' => 'decimal:6',
    ];

    public function definition(): BelongsTo
    {
        return $this->belongsTo(CustomFieldDefinition::class, 'custom_field_definition_id');
    }

    public function valueable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getValueAttribute()
    {
        return $this->value_text
            ?? $this->value_number
            ?? $this->value_boolean
            ?? $this->value_date
            ?? $this->value_json;
    }
}
