<?php

namespace App\Exceptions\Auth;

use Exception;

class UserNotAuthenticatedException extends Exception
{
    protected $message = "Utilisateur non authentifié.";
}
