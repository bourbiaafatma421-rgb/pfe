<?php

namespace App\Exceptions\Staff;

use Exception;

class StaffNotFoundException extends Exception
{
    protected $message = "Staff introuvable.";

}
