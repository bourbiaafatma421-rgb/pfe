<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Collaborateur\CollaborateurController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Role\RoleController;

// ---------------------- Auth ----------------------
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/set-password', [ChangePasswordController::class, 'setPassword']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// Création du premier manager (pas besoin d'autorisation, uniquement au setup)
Route::post('/init/manager', [StaffController::class, 'storeManager']); 

// ---------------------- Staff (Manager) ----------------------
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/staff', [StaffController::class, 'index'])->middleware('can:viewAny,App\Models\User');
    Route::post('/staff', [StaffController::class, 'store'])->middleware('can:create,App\Models\User');
    Route::patch('/staff/{id}', [StaffController::class, 'update'])->middleware('can:update,App\Models\User');
    Route::patch('/staff/{id}/toggle-active', [StaffController::class, 'update'])->middleware('can:update,App\Models\User');
    Route::delete('/staff/{id}', [StaffController::class, 'destroy'])->middleware('can:delete,App\Models\User');
});

// ---------------------- Collaborateurs / RH ----------------------
Route::middleware(['auth:sanctum'])->prefix('collaborateurs')->group(function () {
    Route::post('/', [CollaborateurController::class, 'ajouter'])->middleware('can:create,App\Models\User');
    Route::get('/', [CollaborateurController::class, 'index'])->middleware('can:viewAny,App\Models\User');
    Route::patch('/{collaborateur}', [CollaborateurController::class, 'modifiercollaborateur'])->middleware('can:update,App\Models\User');

    // Gestion des rôles (RH)
    Route::prefix('roles')->group(function () {
        Route::post('/', [RoleController::class, 'ajouter'])->middleware('can:create,App\Models\User');
        Route::get('/', [RoleController::class, 'getall'])->middleware('can:viewAny,App\Models\User');
        Route::patch('/{id}', [RoleController::class, 'modifier'])->middleware('can:update,App\Models\User');
        Route::delete('/{id}', [RoleController::class, 'supprimer'])->middleware('can:delete,App\Models\User');
    });
});

// ---------------------- Profil RH ----------------------
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/rh/profil', [ProfileController::class, 'show'])->middleware('can:view,App\Models\User');
    Route::patch('/rh/profil', [ProfileController::class, 'update'])->middleware('can:update,App\Models\User');
});
