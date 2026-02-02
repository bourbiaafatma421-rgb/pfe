<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CollaborateurController;

Route::post('/collaborateur/ajouter', [CollaborateurController::class, 'ajouter']);
Route::get('/collaborateurs', [CollaborateurController::class, 'index']);
