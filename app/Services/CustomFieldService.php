<?php

namespace App\Services;

use App\Exceptions\CustomFieldException;
use App\Models\CustomFieldDefinition;
use App\Models\CustomFieldGroup;
use App\Models\CustomFieldValue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CustomFieldService
{
    public function listGroupsForWorkspace(int $workspaceId, ?string $search = null): Collection
    {
        return CustomFieldGroup::where('workspace_id', $workspaceId)
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('applies_to', 'like', "%{$search}%");
            })
            ->with('definitions')
            ->orderBy('sort_order')
            ->get();
    }

    public function getDefinitionsForEntity(string $type, int $workspaceId): Collection
    {
        return CustomFieldDefinition::whereHas('group', function ($query) use ($type, $workspaceId) {
            $query->where('workspace_id', $workspaceId)
                ->where('applies_to', $type);
        })
            ->with('group')
            ->orderBy('sort_order')
            ->get();
    }

    public function getValuesForEntity(Model $entity): Collection
    {
        return CustomFieldValue::where('valueable_type', get_class($entity))
            ->where('valueable_id', $entity->id)
            ->with('definition')
            ->get()
            ->keyBy('custom_field_definition_id');
    }

    public function createGroup(array $data): CustomFieldGroup
    {
        return CustomFieldGroup::create($data);
    }

    public function updateGroup(CustomFieldGroup $group, array $data): CustomFieldGroup
    {
        $group->update($data);

        return $group->fresh();
    }

    public function deleteGroup(CustomFieldGroup $group): void
    {
        $group->delete();
    }

    public function createDefinition(array $data): CustomFieldDefinition
    {
        return CustomFieldDefinition::create($data);
    }

    public function updateDefinition(CustomFieldDefinition $definition, array $data): CustomFieldDefinition
    {
        $definition->update($data);

        return $definition->fresh();
    }

    public function deleteDefinition(CustomFieldDefinition $definition): void
    {
        $definition->delete();
    }

    public function saveValues(Model $entity, array $values): void
    {
        try {
            DB::transaction(function () use ($entity, $values) {
                foreach ($values as $definitionId => $rawValue) {
                    $definition = CustomFieldDefinition::find($definitionId);

                    if (! $definition) {
                        continue;
                    }

                    $valueData = [
                        'custom_field_definition_id' => $definitionId,
                        'valueable_type' => get_class($entity),
                        'valueable_id' => $entity->id,
                        'value_text' => null,
                        'value_number' => null,
                        'value_boolean' => null,
                        'value_date' => null,
                        'value_json' => null,
                    ];

                    switch ($definition->field_type) {
                        case 'number':
                            $valueData['value_number'] = $rawValue;
                            break;
                        case 'boolean':
                            $valueData['value_boolean'] = (bool) $rawValue;
                            break;
                        case 'date':
                            $valueData['value_date'] = $rawValue;
                            break;
                        case 'select':
                        case 'multiselect':
                            $valueData['value_json'] = is_array($rawValue) ? $rawValue : [$rawValue];
                            break;
                        default:
                            $valueData['value_text'] = $rawValue;
                    }

                    CustomFieldValue::updateOrCreate(
                        [
                            'custom_field_definition_id' => $definitionId,
                            'valueable_type' => get_class($entity),
                            'valueable_id' => $entity->id,
                        ],
                        $valueData,
                    );
                }
            });
        } catch (\Exception $e) {
            throw CustomFieldException::updateFailed($e->getMessage());
        }
    }
}
