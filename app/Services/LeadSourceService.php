<?php

namespace App\Services;

use App\Exceptions\LeadSourceException;
use App\Models\LeadSource;
use Illuminate\Database\Eloquent\Collection;

class LeadSourceService
{
    public function listForWorkspace(int $workspaceId, ?string $search = null): Collection
    {
        return LeadSource::where('workspace_id', $workspaceId)
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function createLeadSource(array $data): LeadSource
    {
        $existing = LeadSource::where('workspace_id', $data['workspace_id'])
            ->where('name', $data['name'])
            ->exists();

        if ($existing) {
            throw LeadSourceException::nameExists();
        }

        if (! isset($data['sort_order'])) {
            $maxOrder = LeadSource::where('workspace_id', $data['workspace_id'])->max('sort_order') ?? 0;
            $data['sort_order'] = $maxOrder + 1;
        }

        return LeadSource::create($data);
    }

    public function updateLeadSource(LeadSource $leadSource, array $data): LeadSource
    {
        if (isset($data['name']) && $data['name'] !== $leadSource->name) {
            $existing = LeadSource::where('workspace_id', $leadSource->workspace_id)
                ->where('name', $data['name'])
                ->where('id', '!=', $leadSource->id)
                ->exists();

            if ($existing) {
                throw LeadSourceException::nameExists();
            }
        }

        $leadSource->update($data);

        return $leadSource->fresh();
    }

    public function deleteLeadSource(LeadSource $leadSource): void
    {
        $leadSource->delete();
    }
}
