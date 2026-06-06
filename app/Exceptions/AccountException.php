<?php

namespace App\Exceptions;

use Exception;

class AccountException extends Exception
{
    public static function accountNotFound(): self
    {
        return new self('Account not found.');
    }

    public static function contactNotFound(): self
    {
        return new self('Contact not found.');
    }

    public static function cannotDeleteAccount(): self
    {
        return new self('Cannot delete account with existing contacts.');
    }

    public static function invalidWorkspace(): self
    {
        return new self('Invalid workspace.');
    }

    public static function accountCreationFailed(string $message = 'Failed to create account.'): self
    {
        return new self($message);
    }

    public static function contactCreationFailed(string $message = 'Failed to create contact.'): self
    {
        return new self($message);
    }

    public static function accountUpdateFailed(string $message = 'Failed to update account.'): self
    {
        return new self($message);
    }

    public static function contactUpdateFailed(string $message = 'Failed to update contact.'): self
    {
        return new self($message);
    }
}
