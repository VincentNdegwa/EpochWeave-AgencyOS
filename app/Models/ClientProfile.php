<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    public function contacts(): HasMany
    {
        return $this->hasMany(AccountContact::class, 'client_profile_id');
    }
}
