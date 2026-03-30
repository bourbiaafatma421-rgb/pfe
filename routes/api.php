<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Collaborateur\CollaborateurController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Role\RoleController;
use App\Http\Controllers\Document\DocumentController;
use App\Http\Controllers\Signature\SignatureController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\notifications\NotificationController;


// ─── Auth ─────────────────────────────────────────────────────────────────────
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/set-password', [ChangePasswordController::class, 'setPassword']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->get('/user', [ProfileController::class, 'getCurrentUser']);
Route::post('/init/manager', [StaffController::class, 'storeManager']);

// ─── Routes publiques signature (mobile sans auth) ────────────────────────────
Route::get('/sign/{token}', [SignatureController::class, 'verifierToken']);
Route::post('/sign/{token}', [SignatureController::class, 'enregistrerSignature']);

// ─── Staff (Manager) ──────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/staff', [StaffController::class, 'index'])->middleware('can:viewAny,App\Models\User');
    Route::get('/staff/{user}', [StaffController::class, 'show'])->middleware('can:view,user');
    Route::post('/staff', [StaffController::class, 'store'])->middleware('can:create,App\Models\User');
    Route::patch('/staff/{user}', [StaffController::class, 'update'])->middleware('auth:sanctum');
    Route::patch('/staff/{user}/toggle-active', [StaffController::class, 'toggleActive'])->middleware('auth:sanctum');
    Route::delete('/staff/{id}', [StaffController::class, 'destroy'])->middleware('can:delete,App\Models\User');
});

// ─── Collaborateurs / RH ──────────────────────────────────────────────────────
Route::middleware(['auth:sanctum'])->prefix('collaborateurs')->group(function () {
    Route::post('/', [CollaborateurController::class, 'ajouter'])->middleware('can:create,App\Models\User');
    Route::get('/', [CollaborateurController::class, 'index'])->middleware('can:viewAny,App\Models\User');
    Route::patch('/{collaborateur}', [CollaborateurController::class, 'modifiercollaborateur'])->middleware('can:update,collaborateur');
    Route::get('/{id}', [CollaborateurController::class, 'show'])->middleware('auth:sanctum');
});

// ─── Rôles ────────────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum'])->prefix('roles')->group(function () {
    Route::post('/', [RoleController::class, 'ajouter'])->middleware('can:create,App\Models\Role');
    Route::get('/', [RoleController::class, 'getall'])->middleware('can:viewAny,App\Models\Role');
    Route::patch('/role/{role}', [RoleController::class, 'modifier'])->middleware(['auth:sanctum', 'can:update,role']);
    Route::delete('/{role}', [RoleController::class, 'supprimer'])->middleware('can:delete,App\Models\Role');
});

// ─── Documents ────────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum'])->prefix('documents')->group(function () {
    Route::post('/', [DocumentController::class, 'store'])->middleware('can:create,App\Models\Document');
    Route::get('/', [DocumentController::class, 'index'])->middleware('can:viewAny,App\Models\Document');
    Route::get('/{id}/view', [DocumentController::class, 'view']);
    Route::patch('/{id}', [DocumentController::class, 'update']);
    Route::delete('/{id}', [DocumentController::class, 'destroy'])->middleware('can:delete,App\Models\Document');
    Route::post('/sign', [DocumentController::class, 'sign']);
});

// ─── Signature ────────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum'])->prefix('signature')->group(function () {
    Route::get('/token', [SignatureController::class, 'genererToken']);
    Route::get('/status', [SignatureController::class, 'statut']);
    Route::post('/signer/{documentId}', [SignatureController::class, 'signerDocument']);
});

// ─── Dashboard ────────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum'])->prefix('dashboard')->group(function () {
    Route::get('/stats', [DashboardController::class, 'stats']);
});

// ─── Notifications ────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum'])->prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::patch('/read-all', [NotificationController::class, 'marquerToutesLues']);
    Route::patch('/{id}/read', [NotificationController::class, 'marquerLue']);
    Route::delete('/{id}', [NotificationController::class, 'supprimer']);
});

// ─── Profil RH ────────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/rh/profil', [ProfileController::class, 'show']);
    Route::patch('/rh/profil', [ProfileController::class, 'update']);
});