<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laratrust\Models\Team as LaratrustTeam;

class Workspace extends LaratrustTeam
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'currency',
        'white_label',
        'domain',
        'logo_url',
        'primary_color',
    ];

    protected $casts = [
        'white_label' => 'boolean',
    ];
}
