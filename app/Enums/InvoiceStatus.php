<?php

namespace App\Enums;

use JsonSerializable;

enum InvoiceStatus: string implements JsonSerializable
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Paid = 'paid';
    case Void = 'void';
    case Overdue = 'overdue';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Sent => 'Sent',
            self::Paid => 'Paid',
            self::Void => 'Void',
            self::Overdue => 'Overdue',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'bg-secondary text-secondary-foreground',
            self::Sent => 'bg-blue-100 text-blue-900 dark:bg-blue-950/50 dark:text-blue-200',
            self::Paid => 'bg-emerald-100 text-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-200',
            self::Void => 'bg-muted text-muted-foreground',
            self::Overdue => 'bg-red-100 text-red-900 dark:bg-red-950/50 dark:text-red-200',
        };
    }

    public function getVariant(): string
    {
        return match ($this) {
            self::Draft => 'secondary',
            self::Sent => 'default',
            self::Paid => 'default',
            self::Void => 'outline',
            self::Overdue => 'destructive',
        };
    }

    public function getHexColor(): string
    {
        return match ($this) {
            self::Draft => '#6b7280',
            self::Sent => '#3b82f6',
            self::Paid => '#10b981',
            self::Void => '#ef4444',
            self::Overdue => '#f59e0b',
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
