<?php

namespace App\Enums;

use JsonSerializable;

enum EngagementStatus: string implements JsonSerializable
{
    case Planned = 'planned';
    case Completed = 'completed';
    case NoAnswer = 'no_answer';
    case Replied = 'replied';
    case Bounced = 'bounced';

    public function getLabel(): string
    {
        return match ($this) {
            self::Planned => 'Planned',
            self::Completed => 'Completed',
            self::NoAnswer => 'No Answer',
            self::Replied => 'Replied',
            self::Bounced => 'Bounced',
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
