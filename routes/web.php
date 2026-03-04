<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\LibraryController;

Route::get('/', function () {
    return view('home');
});

// AUTORYZACJA WEB
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [AuthController::class, 'webLogin'])->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::post('/register', [AuthController::class, 'webRegister'])->name('register');

// PROTEGROWANE TRASY WEB (sesja)
Route::middleware(['auth'])->group(function () {
    Route::get('/games', function () {
        return view('games');
    })->name('games');

    Route::get('/games/{id}', [GameController::class, 'show'])->name('games.show');
    
    // BIBLIOTEKA
    Route::get('/library', [LibraryController::class, 'index'])->name('library.index');
    Route::post('/library', [LibraryController::class, 'store'])->name('library.store');
    Route::put('/library/{userGame}', [LibraryController::class, 'update'])->name('library.update'); // ZMIENIONE NA PUT
    Route::delete('/library/{userGame}', [LibraryController::class, 'destroy'])->name('library.destroy');
    
    // PROFIL
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // DODANE: Trasa do zmiany hasła dla web
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    
    // ADMIN PANEL (WEB)
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/users', [AdminController::class, 'listUsers'])->name('users');
        Route::post('/users/{id}/toggle', [AdminController::class, 'toggleAdmin'])->name('toggle-admin');
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('destroy');
    });
    
    Route::get('/dashboard', function () {
        return redirect('/games');
    })->name('dashboard');
    
    // WYLOGOWANIE
    Route::post('/logout', [AuthController::class, 'webLogout'])->name('logout');
});