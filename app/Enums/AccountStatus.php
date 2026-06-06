<?php

namespace App\Enums;

use JsonSerializable;

enum AccountStatus: string implements JsonSerializable
{
    case Lead = 'lead';
    case Opportunity = 'opportunity';
    case Client = 'client';
    case Archived = 'archived';

    public function getLabel(): string
    {
        return match ($this) {
            self::Lead => 'Lead',
            self::Opportunity => 'Opportunity',
            self::Client => 'Client',
            self::Archived => 'Archived',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Lead => 'bg-secondary text-secondary-foreground',
            self::Opportunity => 'bg-primary text-primary-foreground',
            self::Client => 'bg-primary text-primary-foreground',
            self::Archived => 'border border-input bg-background text-foreground',
        };
    }

    public function getVariant(): string
    {
        return match ($this) {
            self::Lead => 'secondary',
            self::Opportunity => 'default',
            self::Client => 'default',
            self::Archived => 'outline',
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
