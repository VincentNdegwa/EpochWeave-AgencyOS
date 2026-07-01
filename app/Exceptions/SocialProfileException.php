<?php

namespace App\Exceptions;

use Exception;

class SocialProfileException extends Exception
{
    public static function notFound(): self
    {
        return new self('Social profile not found.');
    }

    public static function creationFailed(string $message = 'Failed to create social profile.'): self
    {
        return new self($message);
    }

    public static function updateFailed(string $message = 'Failed to update social profile.'): self
    {
        return new self($message);
    }

    public static function deletionFailed(string $message = 'Failed to delete social profile.'): self
    {
        return new self($message);
    }
}
