<?php

namespace App\Exceptions\Auth;

use Exception;

class AccountInactiveException extends Exception
{
    protected $message = "Compte désactivé.";
}
