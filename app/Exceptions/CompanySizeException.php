<?php

namespace App\Exceptions;

use Exception;

class CompanySizeException extends Exception
{
    public static function notFound(): self
    {
        return new self('Company size not found.');
    }

    public static function creationFailed(string $message = 'Failed to create company size.'): self
    {
        return new self($message);
    }

    public static function updateFailed(string $message = 'Failed to update company size.'): self
    {
        return new self($message);
    }

    public static function deletionFailed(string $message = 'Failed to delete company size.'): self
    {
        return new self($message);
    }

    public static function labelExists(): self
    {
        return new self('A company size with this label already exists in this workspace.');
    }
}
