<?php

namespace App\Exceptions;

use Exception;

class CustomFieldException extends Exception
{
    public static function groupNotFound(): self
    {
        return new self('Custom field group not found.');
    }

    public static function definitionNotFound(): self
    {
        return new self('Custom field definition not found.');
    }

    public static function creationFailed(string $message = 'Failed to create custom field.'): self
    {
        return new self($message);
    }

    public static function updateFailed(string $message = 'Failed to update custom field.'): self
    {
        return new self($message);
    }

    public static function deletionFailed(string $message = 'Failed to delete custom field.'): self
    {
        return new self($message);
    }

    public static function invalidFieldType(): self
    {
        return new self('Invalid custom field type.');
    }
}
