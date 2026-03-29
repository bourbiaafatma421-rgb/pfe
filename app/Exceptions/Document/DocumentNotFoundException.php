<?php

namespace App\Exceptions\Document;

use Exception;

class DocumentNotFoundException extends Exception
{
    protected $message = 'Le document demandé est introuvable.';
    
}
