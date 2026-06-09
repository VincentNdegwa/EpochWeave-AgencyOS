<?php

namespace App\Enums;

use JsonSerializable;

enum BillingFrequency: string implements JsonSerializable
{
    case None = 'none';
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Yearly = 'yearly';

    public function getLabel(): string
    {
        return match ($this) {
            self::None => 'None',
            self::Daily => 'Daily',
            self::Weekly => 'Weekly',
            self::Monthly => 'Monthly',
            self::Yearly => 'Yearly',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::None => 'bg-muted text-muted-foreground',
            self::Daily => 'bg-amber-100 text-amber-900 dark:bg-amber-950/50 dark:text-amber-200',
            self::Weekly => 'bg-blue-100 text-blue-900 dark:bg-blue-950/50 dark:text-blue-200',
            self::Monthly => 'bg-emerald-100 text-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-200',
            self::Yearly => 'bg-violet-100 text-violet-900 dark:bg-violet-950/50 dark:text-violet-200',
        };
    }

    public function getVariant(): string
    {
        return match ($this) {
            self::None => 'outline',
            default => 'secondary',
        };
    }

    public function jsonSerialize(): mixed
    {
        return [
            'value' => $this->value,
            'label' => $this->getLabel(),
            'color' => $this->getColor(),
            'variant' => $this->getVariant(),
        ];
    }
}
