<?php

namespace App\Services;

use App\Models\ProposalTemplate;
use Exception;
use Illuminate\Support\Collection;

class ProposalTemplateService
{
    public function createTemplate(array $data): ProposalTemplate
    {
        try {
            return ProposalTemplate::create($data);
        } catch (Exception $e) {
            throw new Exception('Failed to create proposal template: '.$e->getMessage());
        }
    }

    public function updateTemplate(ProposalTemplate $template, array $data): ProposalTemplate
    {
        try {
            $template->update($data);

            return $template->fresh();
        } catch (Exception $e) {
            throw new Exception('Failed to update proposal template: '.$e->getMessage());
        }
    }

    public function deleteTemplate(ProposalTemplate $template): void
    {
        try {
            // Check if template is being used by any proposals
            if ($template->proposals()->count() > 0) {
                throw new Exception('Cannot delete template that is being used by proposals.');
            }

            $template->delete();
        } catch (Exception $e) {
            throw new Exception('Failed to delete proposal template: '.$e->getMessage());
        }
    }

    public function getTemplateById(int $id): ?ProposalTemplate
    {
        return ProposalTemplate::find($id);
    }

    public function getTemplatesByWorkspace(int $workspaceId): Collection
    {
        return ProposalTemplate::where('workspace_id', $workspaceId)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function getDefaultTemplate(int $workspaceId): ?ProposalTemplate
    {
        return ProposalTemplate::where('workspace_id', $workspaceId)
            ->where('is_default', true)
            ->first();
    }

    public function setDefaultTemplate(ProposalTemplate $template): ProposalTemplate
    {
        try {
            // Remove default flag from all other templates in the same workspace
            ProposalTemplate::where('workspace_id', $template->workspace_id)
                ->where('id', '!=', $template->id)
                ->update(['is_default' => false]);

            // Set this template as default
            $template->update(['is_default' => true]);

            return $template->fresh();
        } catch (Exception $e) {
            throw new Exception('Failed to set default template: '.$e->getMessage());
        }
    }

    public function duplicateTemplate(ProposalTemplate $template, string $newName): ProposalTemplate
    {
        try {
            $newTemplate = $template->replicate();
            $newTemplate->name = $newName;
            $newTemplate->is_default = false;
            $newTemplate->save();

            return $newTemplate;
        } catch (Exception $e) {
            throw new Exception('Failed to duplicate template: '.$e->getMessage());
        }
    }

    public function updateTemplateContent(ProposalTemplate $template, array $content): ProposalTemplate
    {
        try {
            return $this->updateTemplate($template, ['content' => $content]);
        } catch (Exception $e) {
            throw new Exception('Failed to update template content: '.$e->getMessage());
        }
    }
}
