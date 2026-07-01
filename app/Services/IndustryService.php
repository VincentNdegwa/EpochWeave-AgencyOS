<?php

namespace App\Services;

use App\Exceptions\IndustryException;
use App\Models\Industry;
use Illuminate\Database\Eloquent\Collection;

class IndustryService
{
    public function listForWorkspace(int $workspaceId, ?string $search = null): Collection
    {
        return Industry::where('workspace_id', $workspaceId)
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function createIndustry(array $data): Industry
    {
        $existing = Industry::where('workspace_id', $data['workspace_id'])
            ->where('name', $data['name'])
            ->exists();

        if ($existing) {
            throw IndustryException::nameExists();
        }

        if (! isset($data['sort_order'])) {
            $maxOrder = Industry::where('workspace_id', $data['workspace_id'])->max('sort_order') ?? 0;
            $data['sort_order'] = $maxOrder + 1;
        }

        return Industry::create($data);
    }

    public function updateIndustry(Industry $industry, array $data): Industry
    {
        if (isset($data['name']) && $data['name'] !== $industry->name) {
            $existing = Industry::where('workspace_id', $industry->workspace_id)
                ->where('name', $data['name'])
                ->where('id', '!=', $industry->id)
                ->exists();

            if ($existing) {
                throw IndustryException::nameExists();
            }
        }

        $industry->update($data);

        return $industry->fresh();
    }

    public function deleteIndustry(Industry $industry): void
    {
        $industry->delete();
    }
}
