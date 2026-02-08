<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CollaborateurController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChangePasswordController; 
use App\Models\User;

Route::prefix('collaborateur')->group(function () {
    Route::post('ajouter', [CollaborateurController::class, 'ajouter'])->middleware('auth:sanctum');
    Route::patch('{collaborateur}', [CollaborateurController::class, 'modifiercollaborateur'])->middleware('auth:sanctum');
    Route::get('/', [CollaborateurController::class, 'index'])->middleware('auth:sanctum');
});

//Route::get('/collaborateur/getbynometprenom', [CollaborateurController::class, 'getbynometprenom']);
//Route::get('/collaborateur/getbyetat', [CollaborateurController::class, 'getbyetat']);
//Route::get('/collaborateur/getall', [CollaborateurController::class, 'getall']);
Route::get('/staff', [StaffController::class, 'index']);
Route::post('/staff', [StaffController::class, 'store']);
Route::post('/login', [AuthController::class, 'login']);



