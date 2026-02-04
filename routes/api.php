<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CollaborateurController;

Route::post('/collaborateur/ajouter', [CollaborateurController::class, 'ajouter']);
Route::get('/collaborateur/getbynometprenom', [CollaborateurController::class, 'getbynometprenom']);
Route::get('/collaborateur/getbyetat', [CollaborateurController::class, 'getbyetat']);
Route::get('/collaborateur/getall', [CollaborateurController::class, 'getall']);
Route::patch('/collaborateur/{id}', [CollaborateurController::class, 'modifiercollaborateur']);
