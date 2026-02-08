<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CollaborateurController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChangePasswordController; 
use App\Models\User;

Route::middleware('auth:sanctum')->post(
    '/set-password',
    [ChangePasswordController::class, 'setPassword']
);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::post('/init/manager', [StaffController::class, 'storeManager']);

Route::middleware(['auth:sanctum', 'role:MANAGER'])->group(function () {
    Route::get('/staff', [StaffController::class, 'index']); 
    Route::delete('/staff/{id}', [StaffController::class, 'destroy']);

    Route::post('/staff', [StaffController::class, 'store']);

    Route::patch('/staff/{id}', [StaffController::class, 'update']);
    Route::patch('/staff/{id}/toggle-active', [StaffController::class, 'toggleActive']);

});