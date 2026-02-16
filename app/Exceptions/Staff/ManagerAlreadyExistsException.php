<?php

namespace App\Exceptions\Staff;

use Exception;

class ManagerAlreadyExistsException extends Exception
{
    protected $message = "Un manager existe déjà.";
}
