<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SocialProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'profileable_type',
        'profileable_id',
        'platform',
        'url',
        'handle',
        'followers_count',
        'is_verified',
    ];

    protected $casts = [
        'followers_count' => 'integer',
        'is_verified' => 'boolean',
    ];

    public function profileable(): MorphTo
    {
        return $this->morphTo();
    }
}
