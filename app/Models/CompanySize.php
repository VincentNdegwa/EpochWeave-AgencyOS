<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanySize extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'label',
        'min_employees',
        'max_employees',
        'sort_order',
        'is_default',
    ];

    protected $casts = [
        'min_employees' => 'integer',
        'max_employees' => 'integer',
        'sort_order' => 'integer',
        'is_default' => 'boolean',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public static function getDefaultRecords(): array
    {
        return [
            ['label' => '1-10 employees', 'min_employees' => 1, 'max_employees' => 10, 'sort_order' => 0],
            ['label' => '11-50 employees', 'min_employees' => 11, 'max_employees' => 50, 'sort_order' => 1],
            ['label' => '51-200 employees', 'min_employees' => 51, 'max_employees' => 200, 'sort_order' => 2],
            ['label' => '201-500 employees', 'min_employees' => 201, 'max_employees' => 500, 'sort_order' => 3],
            ['label' => '501-1000 employees', 'min_employees' => 501, 'max_employees' => 1000, 'sort_order' => 4],
            ['label' => '1001-5000 employees', 'min_employees' => 1001, 'max_employees' => 5000, 'sort_order' => 5],
            ['label' => '5001-10000 employees', 'min_employees' => 5001, 'max_employees' => 10000, 'sort_order' => 6],
            ['label' => '10001+ employees', 'min_employees' => 10001, 'max_employees' => null, 'sort_order' => 7],
        ];
    }
}
