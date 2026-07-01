<?php

namespace App\Enums;

use JsonSerializable;

enum EngagementOutcome: string implements JsonSerializable
{
    case Positive = 'positive';
    case Neutral = 'neutral';
    case Negative = 'negative';
    case Voicemail = 'voicemail';

    public function getLabel(): string
    {
        return match ($this) {
            self::Positive => 'Positive',
            self::Neutral => 'Neutral',
            self::Negative => 'Negative',
            self::Voicemail => 'Voicemail',
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
