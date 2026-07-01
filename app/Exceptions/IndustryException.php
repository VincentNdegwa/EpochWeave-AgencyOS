<?php

namespace App\Exceptions;

use Exception;

class IndustryException extends Exception
{
    public static function notFound(): self
    {
        return new self('Industry not found.');
    }

    public static function creationFailed(string $message = 'Failed to create industry.'): self
    {
        return new self($message);
    }

    public static function updateFailed(string $message = 'Failed to update industry.'): self
    {
        return new self($message);
    }

    public static function deletionFailed(string $message = 'Failed to delete industry.'): self
    {
        return new self($message);
    }

    public static function nameExists(): self
    {
        return new self('An industry with this name already exists in this workspace.');
    }
}
