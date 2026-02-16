<?php

namespace App\Exceptions\Role;;

use Exception;

class RoleExistsException extends Exception
{
    protected $message = "Ce rôle existe déjà."; 

    // Tu peux aussi passer un nom spécifique
    public function __construct($roleName = null)
    {
        if ($roleName) {
            $this->message = "Le rôle '{$roleName}' existe déjà. Veuillez choisir un autre nom si pas le meme .";
        }
        parent::__construct($this->message);
    }
}