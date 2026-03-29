<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Collaborateur\CollaborateurController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Role\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Document\DocumentController;
use App\Http\Controllers\Document\DocumentSignatureController;

// ---------------------- Auth ----------------------
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/set-password', [ChangePasswordController::class, 'setPassword']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);


Route::post('/init/manager', [StaffController::class, 'storeManager']); 
Route::middleware('auth:sanctum')->get('/user', [UserController::class, 'me']);

// ---------------------- Manager ----------------------
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/staff', [StaffController::class, 'index'])->middleware('can:viewAny,App\Models\User');
    Route::get('/staff/{user}', [StaffController::class, 'show'])
    ->middleware('can:view,user');
    Route::post('/staff', [StaffController::class, 'store'])->middleware('can:create,App\Models\User');
    Route::patch('/staff/{user}', [StaffController::class, 'update'])->middleware('auth:sanctum');
    Route::patch('/staff/{user}/toggle-active', [StaffController::class, 'toggleActive'])
    ->middleware('auth:sanctum');   
    Route::delete('/staff/{id}', [StaffController::class, 'destroy'])->middleware('can:delete,App\Models\User');
});

//----------------------Gestion des documents----------------------
Route::middleware(['auth:sanctum'])->prefix('documents')->group(function () {
    Route::post('/', [DocumentController::class, 'store'])
        ->middleware('can:create,' . App\Models\Document::class);
    Route::get('/', [DocumentController::class, 'index'])
        ->middleware('can:viewAny,' . App\Models\Document::class);
    Route::get('/{id}/view', [DocumentController::class, 'view']);
    Route::patch('/{id}', [DocumentController::class, 'update'])
        ->middleware('auth:sanctum');
    Route::delete('/{id}', [DocumentController::class, 'destroy'])
        ->middleware('can:delete,App\Models\Document');
});

Route::middleware(['auth:sanctum'])->prefix('signature')->group(function () {
    // Générer token QR pour le collaborateur connecté
    Route::get('/token', [SignatureController::class, 'genererToken']);
    // Statut signature du collaborateur connecté
    Route::get('/status', [SignatureController::class, 'statut']);
    // Signer un document avec sa signature enregistrée
    Route::post('/signer/{documentId}', [SignatureController::class, 'signerDocument']);
});

// ---------------------- Collaborateurs / RH ----------------------
Route::middleware(['auth:sanctum'])->prefix('collaborateurs')->group(function () {

    // Ajouter un collaborateur 
    Route::post('/', [CollaborateurController::class, 'ajouter'])
        ->middleware('can:create,' . App\Models\User::class);

    // Liste des collaborateurs
    Route::get('/', [CollaborateurController::class, 'index'])
        ->middleware('can:viewAny,' . App\Models\User::class);

    // Modifier un collaborateur
    Route::patch('/{collaborateur}', [CollaborateurController::class, 'modifiercollaborateur'])
        ->middleware('can:update,collaborateur');

    //Gestion des rôles
        Route::prefix('roles')->group(function () {
        Route::post('/', [RoleController::class, 'ajouter'])
            ->middleware('can:create,' . App\Models\Role::class);

        Route::get('/', [RoleController::class, 'getall'])
            ->middleware('can:viewAny,' . App\Models\Role::class);

        Route::patch('/{role}', [RoleController::class, 'modifier'])
            ->middleware('can:update,role');

        Route::delete('/{role}', [RoleController::class, 'supprimer'])
            ->middleware('can:delete,role');

    });
});

// ---------------------- Profil RH ----------------------
Route::middleware(['auth:sanctum'])->group(function () {
Route::get('/rh/profil', [ProfileController::class, 'show']);
Route::patch('/rh/profil', [ProfileController::class, 'update']);
});