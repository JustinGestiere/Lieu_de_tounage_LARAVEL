<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FilmController;
use App\Http\Controllers\LocationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Routes accessibles à TOUS les utilisateurs connectés
Route::middleware('auth')->group(function () {
    // Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Consultation et CRÉATION
    Route::resource('film', FilmController::class)->only(['index', 'create', 'store', 'show']);
    Route::resource('location', LocationController::class)->only(['index', 'create', 'store', 'show']);

    // Upvote
    Route::post('/location/{id}/upvote', [LocationController::class, 'upvote'])->name('location.upvote');
});

// Routes réservées aux ADMINISTRATEURS (Modification et Suppression)
Route::middleware(['auth', 'admin'])->group(function () {
    Route::resource('film', FilmController::class)->only(['edit', 'update', 'destroy']);
    Route::resource('location', LocationController::class)->only(['edit', 'update', 'destroy']);
});

require __DIR__ . '/auth.php';
