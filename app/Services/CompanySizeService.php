<?php

namespace App\Services;

use App\Exceptions\CompanySizeException;
use App\Models\CompanySize;
use Illuminate\Database\Eloquent\Collection;

class CompanySizeService
{
    public function listForWorkspace(int $workspaceId, ?string $search = null): Collection
    {
        return CompanySize::where('workspace_id', $workspaceId)
            ->when($search, function ($query) use ($search) {
                $query->where('label', 'like', "%{$search}%");
            })
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();
    }

    public function createCompanySize(array $data): CompanySize
    {
        $existing = CompanySize::where('workspace_id', $data['workspace_id'])
            ->where('label', $data['label'])
            ->exists();

        if ($existing) {
            throw CompanySizeException::labelExists();
        }

        if (! isset($data['sort_order'])) {
            $maxOrder = CompanySize::where('workspace_id', $data['workspace_id'])->max('sort_order') ?? 0;
            $data['sort_order'] = $maxOrder + 1;
        }

        return CompanySize::create($data);
    }

    public function updateCompanySize(CompanySize $companySize, array $data): CompanySize
    {
        if (isset($data['label']) && $data['label'] !== $companySize->label) {
            $existing = CompanySize::where('workspace_id', $companySize->workspace_id)
                ->where('label', $data['label'])
                ->where('id', '!=', $companySize->id)
                ->exists();

            if ($existing) {
                throw CompanySizeException::labelExists();
            }
        }

        $companySize->update($data);

        return $companySize->fresh();
    }

    public function deleteCompanySize(CompanySize $companySize): void
    {
        $companySize->delete();
    }
}
