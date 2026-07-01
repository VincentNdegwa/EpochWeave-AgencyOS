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

    public function projectStatuses(): HasMany
    {
        return $this->hasMany(ProjectStatus::class);
    }

    public function invoiceStatuses(): HasMany
    {
        return $this->hasMany(InvoiceStatus::class);
    }

    public function industries(): HasMany
    {
        return $this->hasMany(Industry::class);
    }

    public function leadSources(): HasMany
    {
        return $this->hasMany(LeadSource::class);
    }

    public function companySizes(): HasMany
    {
        return $this->hasMany(CompanySize::class);
    }

    public function customFieldGroups(): HasMany
    {
        return $this->hasMany(CustomFieldGroup::class);
    }
}
