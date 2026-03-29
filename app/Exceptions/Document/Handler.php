<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof AuthorizationException) {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        return parent::render($request, $exception);
    }
}