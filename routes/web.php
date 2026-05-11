<?php

use App\Http\Controllers\FilmController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StripeController;
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

    // Films (Toujours séparé : créer pour tous, modifier pour admin)
    Route::resource('film', FilmController::class)->only(['index', 'create', 'store', 'show']);

    // Locations (Nouveau : tout est accessible, la sécurité est dans le contrôleur)
    Route::resource('location', LocationController::class);

    // Upvote
    Route::post('/location/{id}/upvote', [LocationController::class, 'upvote'])->name('location.upvote');

    // Stripe Checkout
    Route::get('/payment', [StripeController::class, 'index'])->name('payment.index')->middleware('subscribed');
    Route::post('/subscribe', [StripeController::class, 'checkout'])->name('subscribe');
    Route::get('/stripe/success', [StripeController::class, 'success'])->name('stripe.success');
    Route::get('/stripe/cancel', [StripeController::class, 'cancel'])->name('stripe.cancel');
});

// Routes réservées aux ADMINISTRATEURS
Route::middleware(['auth', 'admin'])->group(function () {
    Route::resource('film', FilmController::class)->only(['edit', 'update', 'destroy']);
    // location n'est plus ici car on gère la permission par auteur
});

require __DIR__.'/auth.php';
