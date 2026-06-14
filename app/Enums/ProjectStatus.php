<?php

namespace App\Enums;

use JsonSerializable;

enum ProjectStatus: string implements JsonSerializable
{
    case Active = 'active';
    case OnHold = 'on_hold';
    case Completed = 'completed';
    case Archived = 'archived';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::OnHold => 'On Hold',
            self::Completed => 'Completed',
            self::Archived => 'Archived',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'bg-emerald-100 text-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-200',
            self::OnHold => 'bg-amber-100 text-amber-900 dark:bg-amber-950/50 dark:text-amber-200',
            self::Completed => 'bg-blue-100 text-blue-900 dark:bg-blue-950/50 dark:text-blue-200',
            self::Archived => 'bg-muted text-muted-foreground',
        };
    }

    public function getVariant(): string
    {
        return match ($this) {
            self::Active => 'default',
            self::OnHold => 'default',
            self::Completed => 'default',
            self::Archived => 'outline',
        };
    }

    public function getHexColor(): string
    {
        return match ($this) {
            self::Active => '#10b981',
            self::OnHold => '#f59e0b',
            self::Completed => '#3b82f6',
            self::Archived => '#6b7280',
        };
    }

    public function jsonSerialize(): mixed
    {
        return [
            'value' => $this->value,
            'label' => $this->getLabel(),
            'color' => $this->getColor(),
            'variant' => $this->getVariant(),
            'hexColor' => $this->getHexColor(),
        ];
    }
}
