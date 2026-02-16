<?php

namespace App\Exceptions\Staff;

use Exception;

class StaffUpdateForbiddenException extends Exception
{
    public function __construct($message = "Vous n'êtes pas autorisé à modifier ce RH.")
    {
        parent::__construct($message);
    }
}
