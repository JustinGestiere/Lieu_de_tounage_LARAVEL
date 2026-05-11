<?php

use App\Models\Film;
use App\Models\Location;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Tests Feature : Locations (CRUD + Upvote)
|--------------------------------------------------------------------------
|
| Ces tests vérifient le cycle de vie complet des lieux de tournage :
| listing, création, édition (par auteur ou admin), suppression,
| et le système d'upvote avec protection contre le double vote.
|
*/

// ── INDEX : affiche les locations ──
it('affiche la liste des locations', function () {
    $user = User::factory()->create();
    $film = Film::factory()->create();
    Location::factory()->create([
        'film_id' => $film->id,
        'user_id' => $user->id,
        'name' => 'Tour Eiffel',
    ]);

    $response = $this->actingAs($user)->get('/location');

    $response->assertStatus(200);
    $response->assertSee('Tour Eiffel');
});

// ── STORE : créer une location ──
it('permet de créer une location', function () {
    $user = User::factory()->create();
    $film = Film::factory()->create();

    $response = $this->actingAs($user)->post('/location', [
        'film_id' => $film->id,
        'user_id' => $user->id,
        'name' => 'Château de Versailles',
        'city' => 'Versailles',
        'country' => 'France',
        'description' => 'Lieu magnifique',
    ]);

    $response->assertRedirect('/location');
    $this->assertDatabaseHas('locations', ['name' => 'Château de Versailles']);
});

// ── EDIT : l'auteur peut modifier sa propre location ──
it('permet à l\'auteur de modifier sa location', function () {
    $user = User::factory()->create();
    $film = Film::factory()->create();
    $location = Location::factory()->create([
        'user_id' => $user->id,
        'film_id' => $film->id,
    ]);

    $response = $this->actingAs($user)->get("/location/{$location->id}/edit");

    $response->assertStatus(200);
});

// ── EDIT : un autre utilisateur ne peut PAS modifier ──
it('interdit à un autre utilisateur de modifier une location', function () {
    $author = User::factory()->create();
    $otherUser = User::factory()->create(['is_admin' => false]);
    $film = Film::factory()->create();
    $location = Location::factory()->create([
        'user_id' => $author->id,
        'film_id' => $film->id,
    ]);

    $response = $this->actingAs($otherUser)->get("/location/{$location->id}/edit");

    $response->assertStatus(403);
});

// ── DESTROY : l'auteur peut supprimer sa location ──
it('permet à l\'auteur de supprimer sa location', function () {
    $user = User::factory()->create();
    $film = Film::factory()->create();
    $location = Location::factory()->create([
        'user_id' => $user->id,
        'film_id' => $film->id,
    ]);

    $response = $this->actingAs($user)->delete("/location/{$location->id}");

    $response->assertRedirect('/location');
    $this->assertDatabaseMissing('locations', ['id' => $location->id]);
});

// ── UPVOTE : un utilisateur peut voter une fois ──
it('permet de voter pour une location', function () {
    $user = User::factory()->create();
    $film = Film::factory()->create();
    $location = Location::factory()->create([
        'film_id' => $film->id,
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->post("/location/{$location->id}/upvote");

    $response->assertRedirect();
    $this->assertDatabaseHas('location_votes', [
        'user_id' => $user->id,
        'location_id' => $location->id,
    ]);
});

// ── UPVOTE : double vote impossible ──
it('empêche le double vote', function () {
    $user = User::factory()->create();
    $film = Film::factory()->create();
    $location = Location::factory()->create([
        'film_id' => $film->id,
        'user_id' => $user->id,
    ]);

    // Premier vote
    $this->actingAs($user)->post("/location/{$location->id}/upvote");
    // Deuxième vote (ne doit pas créer de doublon)
    $this->actingAs($user)->post("/location/{$location->id}/upvote");

    $votesCount = \Illuminate\Support\Facades\DB::table('location_votes')
        ->where('user_id', $user->id)
        ->where('location_id', $location->id)
        ->count();

    expect($votesCount)->toBe(1);
});
