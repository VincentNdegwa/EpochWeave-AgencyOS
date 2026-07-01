<?php

namespace App\Exceptions;

use Exception;

class LeadSourceException extends Exception
{
    public static function notFound(): self
    {
        return new self('Lead source not found.');
    }

    public static function creationFailed(string $message = 'Failed to create lead source.'): self
    {
        return new self($message);
    }

    public static function updateFailed(string $message = 'Failed to update lead source.'): self
    {
        return new self($message);
    }

    public static function deletionFailed(string $message = 'Failed to delete lead source.'): self
    {
        return new self($message);
    }

    public static function nameExists(): self
    {
        return new self('A lead source with this name already exists in this workspace.');
    }
}
