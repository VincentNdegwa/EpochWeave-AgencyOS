<?php

namespace App\Enums;

use JsonSerializable;

enum BillingType: string implements JsonSerializable
{
    case OneTime = 'one_time';
    case Recurring = 'recurring';

    public function getLabel(): string
    {
        return match ($this) {
            self::OneTime => 'One Time',
            self::Recurring => 'Recurring',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::OneTime => 'bg-secondary text-secondary-foreground',
            self::Recurring => 'bg-primary text-primary-foreground',
        };
    }

    public function getVariant(): string
    {
        return match ($this) {
            self::OneTime => 'secondary',
            self::Recurring => 'default',
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
