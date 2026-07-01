<?php

namespace App\Exceptions;

use Exception;

class AddressException extends Exception
{
    public static function addressNotFound(): self
    {
        return new self('Address not found.');
    }

    public static function creationFailed(string $message = 'Failed to create address.'): self
    {
        return new self($message);
    }

    public static function updateFailed(string $message = 'Failed to update address.'): self
    {
        return new self($message);
    }

    public static function deletionFailed(string $message = 'Failed to delete address.'): self
    {
        return new self($message);
    }

    public static function unauthorized(): self
    {
        return new self('You do not have permission to manage this address.');
    }
}
