<?php

namespace App\Enums;

use JsonSerializable;

enum EngagementType: string implements JsonSerializable
{
    case Call = 'call';
    case Email = 'email';
    case LinkedIn = 'linkedin';
    case Sms = 'sms';
    case Meeting = 'meeting';
    case Note = 'note';

    public function getLabel(): string
    {
        return match ($this) {
            self::Call => 'Call',
            self::Email => 'Email',
            self::LinkedIn => 'LinkedIn',
            self::Sms => 'SMS',
            self::Meeting => 'Meeting',
            self::Note => 'Note',
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
