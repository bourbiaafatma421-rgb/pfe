<?php

namespace App\Exceptions\Auth;

use Exception;

class PasswordAlreadyChangedException extends Exception
{
    protected $message = "Le mot de passe a déjà été défini.";
}
