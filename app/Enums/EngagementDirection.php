<?php

namespace App\Enums;

use JsonSerializable;

enum EngagementDirection: string implements JsonSerializable
{
    case Outbound = 'outbound';
    case Inbound = 'inbound';

    public function getLabel(): string
    {
        return match ($this) {
            self::Outbound => 'Outbound',
            self::Inbound => 'Inbound',
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
