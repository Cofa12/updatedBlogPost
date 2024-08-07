<?php

namespace App\Exceptions;

use Exception;

class UnauthenticatedUserException extends Exception
{
    protected $message;

    public function __construct($message = "Token is missing or invalid")
    {
        $this->message = $message;
        parent::__construct($message);
    }

    public function getCustomMessage()
    {
        return $this->message;
    }
}
