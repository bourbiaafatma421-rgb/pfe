<?php

namespace App\Exceptions\Auth;

use Exception;

class UserNotFoundException extends Exception
{
    protected $message = "Utilisateur introuvable ou identifiants invalides.";

}
