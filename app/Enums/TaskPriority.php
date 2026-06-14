<?php

namespace App\Enums;

use JsonSerializable;

enum TaskPriority: string implements JsonSerializable
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';

    public function getLabel(): string
    {
        return match ($this) {
            self::Low => 'Low',
            self::Medium => 'Medium',
            self::High => 'High',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Low => 'bg-slate-100 text-slate-900 dark:bg-slate-950/50 dark:text-slate-200',
            self::Medium => 'bg-amber-100 text-amber-900 dark:bg-amber-950/50 dark:text-amber-200',
            self::High => 'bg-red-100 text-red-900 dark:bg-red-950/50 dark:text-red-200',
        };
    }

    public function getVariant(): string
    {
        return match ($this) {
            self::Low => 'outline',
            self::Medium => 'default',
            self::High => 'destructive',
        };
    }

    public function getHexColor(): string
    {
        return match ($this) {
            self::Low => '#64748b',
            self::Medium => '#f59e0b',
            self::High => '#ef4444',
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
