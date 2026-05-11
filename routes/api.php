<?php

use App\Models\Film;
use Illuminate\Support\Facades\Route;

Route::post('/login', function () {
    $credentials = request(['email', 'password']);

    if (! $token = auth('api')->attempt($credentials)) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    return response()->json(['token' => $token]);
});

Route::middleware(['auth:api', 'subscribed'])
    ->get('/films/{film}/locations', function ($filmId) {

        $film = Film::with('locations')->findOrFail($filmId);

        return response()->json([
            'film' => [
                'id' => $film->id,
                'title' => $film->title,
                'year' => $film->year,
            ],
            'locations' => $film->locations->map(function ($loc) {
                return [
                    'name' => $loc->name,
                    'city' => $loc->city,
                    'country' => $loc->country,
                    'upvotes' => $loc->upvotes_count,
                ];
            }),
        ]);
    });
