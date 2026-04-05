<?php

namespace App\Exceptions\Document;

use Exception;

class DocumentDeletionException extends Exception
{
    protected $message = 'Erreur lors de la suppression du document.';
}
