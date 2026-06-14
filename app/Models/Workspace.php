<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function settings(): HasMany
    {
        return $this->hasMany(WorkspaceSetting::class);
    }

    public function proposalStatuses(): HasMany
    {
        return $this->hasMany(ProposalStatus::class);
    }

    public function taskStatuses(): HasMany
    {
        return $this->hasMany(TaskStatus::class);
    }
}
