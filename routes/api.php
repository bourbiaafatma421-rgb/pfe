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
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Notifications\NotificationController;
use App\Http\Controllers\Profil\ProfilController;
use App\Http\Controllers\RHIA\OnboardingRHController;
use App\Http\Controllers\RHIA\OnboardingValidationController;
use App\Http\Controllers\Dashboard\DashboardCollaborateurController;
use App\Http\Controllers\Cv\CvController;
use App\Http\Controllers\CollaboratteurIA\OnboardingCollaborateurController;
use App\Http\Controllers\Messaging\MessagingController;
use App\Http\Controllers\CollaboratteurIA\AvisController;


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
    Route::get('/staff', [StaffController::class, 'index']);
    Route::get('/staff/{user}', [StaffController::class, 'show']);
    Route::post('/staff', [StaffController::class, 'store']);
    Route::patch('/staff/{user}', [StaffController::class, 'update']);
    Route::patch('/staff/{user}/toggle-active', [StaffController::class, 'toggleActive']);
    Route::delete('/staff/{id}', [StaffController::class, 'destroy']);
});

// ─── Collaborateurs / RH ──────────────────────────────────────────────────────
Route::middleware(['auth:sanctum'])->prefix('collaborateurs')->group(function () {

    Route::prefix('roles')->group(function () {
        Route::get('/',         [RoleController::class, 'getall']);
        Route::post('/',        [RoleController::class, 'ajouter']);
        Route::patch('/{role}', [RoleController::class, 'modifier']);
        Route::delete('/{role}',[RoleController::class, 'supprimer']);
    });

    Route::post('/',                 [CollaborateurController::class, 'ajouter'])->middleware('can:create,App\Models\User');
    Route::get('/',                  [CollaborateurController::class, 'index'])->middleware('can:viewAny,App\Models\User');
    Route::get('/{id}',              [CollaborateurController::class, 'show']);
    Route::patch('/{collaborateur}', [CollaborateurController::class, 'modifiercollaborateur']);

    Route::post('/onboarding/{id}/avis', [AvisController::class, 'store']);
    Route::get('/onboarding/{id}/avis',  [AvisController::class, 'show']);

});
// ─── Profil ───────────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum'])->prefix('profil')->group(function () {
    Route::get('/', [ProfilController::class, 'show']);
    Route::patch('/telephone', [ProfilController::class, 'updateTelephone']);
    Route::post('/avatar', [ProfilController::class, 'uploadAvatar']);
    Route::delete('/avatar', [ProfilController::class, 'deleteAvatar']);
});

// ─── Documents ────────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum'])->prefix('documents')->group(function () {
    Route::post('/', [DocumentController::class, 'store'])->middleware('can:create,App\Models\Document');
    Route::get('/', [DocumentController::class, 'index'])->middleware('can:viewAny,App\Models\Document');
    Route::get('/mes-documents', [DocumentController::class, 'mesDocuments']);
    Route::post('/sign', [DocumentController::class, 'sign']);
    Route::get('/{id}/view', [DocumentController::class, 'view']);
    Route::patch('/{id}', [DocumentController::class, 'update']);
    Route::delete('/{id}', [DocumentController::class, 'destroy'])->middleware('can:delete,App\Models\Document');
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
    Route::get('/onboarding-progress', [DashboardController::class, 'onboardingProgress']);
});

Route::get('/dashboard/collaborateur', [DashboardCollaborateurController::class, 'index'])
    ->middleware(['auth:sanctum']);

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

// ─── CV ───────────────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum'])->prefix('cv')->group(function () {
    Route::post('/upload', [CvController::class, 'upload']);
    Route::post('/validate', [CvController::class, 'validateCv']);
    Route::get('/', [CvController::class, 'show']);
});

// ─── Onboarding RH ───────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum'])->prefix('onboarding')->group(function () {
    Route::get('/colab/status',           [OnboardingRHController::class, 'colabStatus']);
    Route::get('/responsables',           [OnboardingRHController::class, 'getResponsables']); // ← monter ici
    Route::get('/',                       [OnboardingRHController::class, 'index']);
    Route::get('/{onboarding}',           [OnboardingRHController::class, 'show']);
    Route::post('/user/{user}/generer',   [OnboardingRHController::class, 'generer']);
    Route::patch('/{onboarding}/valider', [OnboardingRHController::class, 'valider']);
    Route::post('/{onboarding}/tasks',    [OnboardingRHController::class, 'addTask']);
    Route::patch('/tasks/{task}',         [OnboardingRHController::class, 'updateTask']);
    Route::delete('/tasks/{task}',        [OnboardingRHController::class, 'deleteTask']);
});

// ─── Onboarding RH — Validation tâches collaborateur ─────────────────────────
Route::middleware(['auth:sanctum'])->prefix('rh')->group(function () {
    Route::get('/tasks/en-validation',    [OnboardingValidationController::class, 'index']);
    Route::patch('/tasks/{task}/valider', [OnboardingValidationController::class, 'valider']);
    Route::patch('/tasks/{task}/rejeter', [OnboardingValidationController::class, 'rejeter']);
});

// ─── Collaborateur IA — Plan + Tâches + Commentaires ─────────────────────────
Route::middleware(['auth:sanctum'])->prefix('my')->group(function () {
    Route::get('/integration-plan',                   [OnboardingCollaborateurController::class, 'myPlan']);
    Route::get('/formations',                         [OnboardingCollaborateurController::class, 'myFormations']); // ← nouveau
    Route::patch('/tasks/{task}',                     [OnboardingCollaborateurController::class, 'updateMyTask']);
    Route::post('/tasks/{task}/comments',             [OnboardingCollaborateurController::class, 'addComment']);
    Route::delete('/comments/{comment}',              [OnboardingCollaborateurController::class, 'deleteComment']);
    Route::get('/tasks/{task}/attachments/{comment}', [OnboardingCollaborateurController::class, 'downloadAttachment']);
    Route::get('/suivis',                             [OnboardingCollaborateurController::class, 'mesSuivis']);

});
Route::middleware(['auth:sanctum'])->prefix('messaging')->name('messaging.')->group(function () {
        Route::get('unread-count',                           [MessagingController::class, 'unreadCount'])->name('unread');
        Route::get('conversations',                          [MessagingController::class, 'index'])->name('conversations.index');
        Route::post('conversations',                         [MessagingController::class, 'store'])->name('conversations.store');
        Route::get('conversations/{conversation}/messages',  [MessagingController::class, 'messages'])->name('messages.index');
        Route::post('conversations/{conversation}/messages', [MessagingController::class, 'send'])->name('messages.send');
        Route::get('collaborateurs/search', [MessagingController::class, 'searchCollaborateurs']);

    });