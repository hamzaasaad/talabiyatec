<?php

namespace App\Exceptions;

use Exception;

class InvalidCredentialsException extends Exception
{
    protected $message;
    protected $code;

    public function __construct(
        string $message = 'Invalid email or password.',
        int $code = 401
    ) {
        parent::__construct($message, $code);
    }
}
