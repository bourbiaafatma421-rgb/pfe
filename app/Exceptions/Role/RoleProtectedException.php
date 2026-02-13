<?php

namespace App\Exceptions\Role;;

use Exception;

class RoleProtectedException extends Exception
{
    protected $message = "Suppression impossible, des utilisateurs sont associés à ce rôle.";
    public function __construct($roleName = null){
        if ($roleName) {
            $this->message = "Le rôle '{$roleName}' est protégé. Modification ou suppression interdite.";
        }
        parent::__construct($this->message);
    }

}
