<?php
namespace App\Exceptions;

use Exception;

class InvalidRefreshTokenException extends Exception
{
    protected $message = 'Invalid or expired refresh token';
}