<?php

namespace App\Exceptions\Role;;

use Exception;

class RoleHasUsersException extends Exception
{
     public $users;
    public function __construct($users){
        $this->users = $users;
        parent::__construct("Suppression impossible, des utilisateurs sont associés à ce rôle.");
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> origin/main
