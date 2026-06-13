<?php

namespace App\Enums;

use JsonSerializable;

enum DiscountType: string implements JsonSerializable
{
    case None = 'none';
    case Percentage = 'percentage';
    case Fixed = 'fixed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Percentage => 'Percentage',
            self::Fixed => 'Fixed',
            self::None => 'None',
        };
    }

    public function jsonSerialize(): mixed
    {
        return [
            'value' => $this->value,
            'label' => $this->getLabel(),
        ];
    }
}
