<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CollaborateurController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\AuthController;


Route::get('/staff', [StaffController::class, 'index']);
Route::post('/staff', [StaffController::class, 'store']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'checkrole:rh'])->group(function () {
    Route::post('/collaborateur/ajouter', [CollaborateurController::class, 'ajouter']);
    Route::get('/collaborateur/getbynometprenom', [CollaborateurController::class, 'getbynometprenom']);
    Route::get('/collaborateur/getbyetat', [CollaborateurController::class, 'getbyetat']);
    Route::get('/collaborateur/getall', [CollaborateurController::class, 'getall']);
    Route::patch('/collaborateur/{id}', [CollaborateurController::class, 'modifiercollaborateur']);
});
Route::middleware(['auth:sanctum', 'checkrole:manager'])->group(function () {
    Route::post('/collaborateur/ajouter', [CollaborateurController::class, 'ajouter']);
    Route::patch('/collaborateur/{id}', [CollaborateurController::class, 'modifiercollaborateur']);
    Route::get('/staff', [StaffController::class, 'index']);
});
