<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CollaborateurController;
use App\Http\Controllers\AuthController;

/*Route::prefix('collaborateur')->group(function () {
    Route::post('ajouter', [CollaborateurController::class, 'ajouter'])->middleware('auth:sanctum');
    Route::patch('{collaborateur}', [CollaborateurController::class, 'modifiercollaborateur'])->middleware('auth:sanctum');
    Route::get('/', [CollaborateurController::class, 'index'])->middleware('auth:sanctum');
});*/


Route::post('/login', [AuthController::class, 'login']);
Route::post('/ajouterrole', [App\Http\Controllers\Role\RoleController::class, 'ajouter']);
Route::get('/getallroles', [App\Http\Controllers\Role\RoleController::class, 'getall']);
Route::patch('/modifierrole/{id}', [App\Http\Controllers\Role\RoleController::class, 'modifier']);
Route::delete('/supprimerrole/{id}', [App\Http\Controllers\Role\RoleController::class, 'supprimer']);
Route::post('/ajoutercollaborateur', [App\Http\Controllers\Collaborateur\CollaborateurController::class, 'ajouter']);
Route::get('/getallcollaborateurs', [App\Http\Controllers\Collaborateur\CollaborateurController::class, 'index']);




