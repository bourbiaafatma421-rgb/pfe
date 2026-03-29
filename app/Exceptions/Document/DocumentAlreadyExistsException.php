<?php

namespace App\Exceptions\Document;

use Exception;

class DocumentAlreadyExistsException extends Exception
{
    protected $message = 'Le document existe déjà.';
}
