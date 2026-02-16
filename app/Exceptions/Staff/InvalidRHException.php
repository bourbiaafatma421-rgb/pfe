<?php

namespace App\Exceptions\Staff;

use Exception;

class InvalidRHException extends Exception
{
    protected $message = "L’utilisateur doit être un RH.";
}
