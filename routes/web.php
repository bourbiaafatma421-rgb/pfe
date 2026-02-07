<?php

use App\Http\Controllers\CollaborateurController;
use Illuminate\Support\Facades\Route;

Route::get('/test-collaborateurs', [CollaborateurController::class, 'index']);
