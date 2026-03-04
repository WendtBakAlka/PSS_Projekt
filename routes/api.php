<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LibraryController; // ← DODAJ TĘ LINIĘ!
use App\Http\Controllers\Api\GameApiController;

// PUBLICZNE API
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/games/search', [GameApiController::class, 'search']);

// PROTEGROWANE API (Sanctum token)
Route::middleware('auth:sanctum')->group(function () {
    // AUTORYZACJA
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/check-auth', function () {
        return response()->json(['authenticated' => true]);
    });
    
    // PROFIL API
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'apiShow']);          // GET /api/profile
        Route::patch('/', [ProfileController::class, 'apiUpdate']);      // PATCH /api/profile
        Route::delete('/', [ProfileController::class, 'apiDestroy']);    // DELETE /api/profile
        Route::patch('/password', [ProfileController::class, 'updatePassword']); // już masz
    });
    
    // ADMIN API
    Route::middleware('admin')->prefix('admin')->group(function () {
    	Route::get('/users', [AdminController::class, 'listUsers']);
    	Route::get('/users/{id}', [AdminController::class, 'apiShowUser']);      // ← DODANE
    	Route::patch('/users/{id}', [AdminController::class, 'apiUpdateUser']);  // ← DODANE
    	Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
    	Route::patch('/users/{id}/toggle-admin', [AdminController::class, 'apiToggleAdmin']);
    });

    // BIBLIOTEKA API - POPRAWIONE!
    Route::get('/library', [LibraryController::class, 'apiIndex']);     // ← apiIndex
    Route::post('/library', [LibraryController::class, 'apiStore']);    // ← apiStore
    Route::put('/library/{id}', [LibraryController::class, 'apiUpdate']);     // ← apiUpdate
    Route::delete('/library/{id}', [LibraryController::class, 'apiDestroy']); // ← apiDestroy
});