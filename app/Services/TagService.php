<?php

namespace App\Services;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

class TagService
{
    public function listForWorkspace(int $workspaceId): Collection
    {
        return Tag::query()
            ->where('workspace_id', $workspaceId)
            ->orderBy('name')
            ->get();
    }

    public function createTag(array $data): Tag
    {
        return Tag::create($data);
    }

    public function updateTag(Tag $tag, array $data): Tag
    {
        $tag->update($data);

        return $tag->fresh();
    }

    public function deleteTag(Tag $tag): void
    {
        $tag->delete();
    }
}
