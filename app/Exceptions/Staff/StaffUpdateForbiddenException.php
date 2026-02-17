<?php

namespace App\Exceptions\Staff;

use Exception;

class StaffUpdateForbiddenException extends Exception
{
    protected $message = "Vous n'êtes pas autorisé à modifier ce RH.";
}
