<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ActivityService
{
    public function record(
        Model $subject,
        string $type,
        ?string $description = null,
        array $properties = [],
        ?int $userId = null,
        ?int $workspaceId = null
    ): Activity {
        $workspaceId ??= $subject->workspace_id ?? null;
        $userId ??= Auth::id();

        return Activity::create([
            'workspace_id' => $workspaceId,
            'user_id' => $userId,
            'type' => $type,
            'subject_type' => get_class($subject),
            'subject_id' => $subject->getKey(),
            'description' => $description ?? $this->generateDescription($subject, $type, $properties),
            'properties' => $properties,
        ]);
    }

    public function created(Model $subject, ?string $description = null, array $properties = []): Activity
    {
        return $this->record($subject, $this->typeFor('created', $subject), $description, $properties);
    }

    public function updated(Model $subject, ?string $description = null, array $properties = []): Activity
    {
        return $this->record($subject, $this->typeFor('updated', $subject), $description, $properties);
    }

    public function deleted(Model $subject, ?string $description = null, array $properties = []): Activity
    {
        return $this->record($subject, $this->typeFor('deleted', $subject), $description, $properties);
    }

    public function viewed(Model $subject, ?string $description = null, array $properties = []): Activity
    {
        return $this->record($subject, $this->typeFor('viewed', $subject), $description, $properties);
    }

    public function sent(Model $subject, ?string $description = null, array $properties = []): Activity
    {
        return $this->record($subject, $this->typeFor('sent', $subject), $description, $properties);
    }

    public function accepted(Model $subject, ?string $description = null, array $properties = []): Activity
    {
        return $this->record($subject, $this->typeFor('accepted', $subject), $description, $properties);
    }

    public function declined(Model $subject, ?string $description = null, array $properties = []): Activity
    {
        return $this->record($subject, $this->typeFor('declined', $subject), $description, $properties);
    }

    public function paid(Model $subject, ?string $description = null, array $properties = []): Activity
    {
        return $this->record($subject, $this->typeFor('paid', $subject), $description, $properties);
    }

    private function typeFor(string $action, Model $subject): string
    {
        $entity = strtolower(class_basename(get_class($subject)));

        return "{$entity}.{$action}";
    }

    private function generateDescription(Model $subject, string $type, array $properties): string
    {
        $entity = class_basename(get_class($subject));
        $name = $this->resolveName($subject);
        $action = Str::afterLast($type, '.');
        $actor = Auth::user()?->name ?? 'System';

        $descriptions = [
            'created' => "{$entity} '{$name}' was created by {$actor}.",
            'updated' => "{$entity} '{$name}' was updated by {$actor}.",
            'deleted' => "{$entity} '{$name}' was deleted by {$actor}.",
            'viewed' => "{$entity} '{$name}' was viewed.",
            'sent' => "{$entity} '{$name}' was sent by {$actor}.",
            'accepted' => "{$entity} '{$name}' was accepted.",
            'declined' => "{$entity} '{$name}' was declined.",
            'paid' => "{$entity} '{$name}' was marked as paid.",
        ];

        return $descriptions[$action] ?? "{$entity} '{$name}' {$action}.";
    }

    private function resolveName(Model $subject): string
    {
        return $subject->name
            ?? $subject->title
            ?? $subject->company_name
            ?? $subject->invoice_number
            ?? $subject->proposal_number
            ?? ('#' . $subject->getKey());
    }
}

