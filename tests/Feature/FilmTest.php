<?php

use App\Models\Film;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Tests Feature : Films (CRUD)
|--------------------------------------------------------------------------
|
| Ces tests vérifient que les routes de gestion des films fonctionnent
| correctement : listing, création, modification et suppression.
| On utilise RefreshDatabase (configuré dans Pest.php) pour repartir
| d'une base propre à chaque test.
|
*/

// ── INDEX : un utilisateur connecté voit la liste des films ──
it('affiche la liste des films pour un utilisateur connecté', function () {
    $user = User::factory()->create();
    Film::factory()->create(['titre' => 'Inception', 'annee' => 2010]);

    $response = $this->actingAs($user)->get('/film');

    $response->assertStatus(200);
    $response->assertSee('Inception');
});

// ── INDEX : un visiteur non connecté est redirigé vers le login ──
it('redirige un visiteur non connecté vers le login', function () {
    $response = $this->get('/film');

    $response->assertRedirect('/login');
});

// ── CREATE : le formulaire de création s'affiche ──
it('affiche le formulaire de création de film', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/film/create');

    $response->assertStatus(200);
});

// ── STORE : un utilisateur peut créer un film ──
it('permet de créer un film', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/film', [
        'titre' => 'Matrix',
        'annee' => 1999,
        'synopsis' => 'Un programmeur découvre la réalité.',
    ]);

    $response->assertRedirect('/film');
    $this->assertDatabaseHas('films', ['titre' => 'Matrix']);
});

// ── STORE : la validation refuse un film sans titre ──
it('refuse de créer un film sans titre', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/film', [
        'annee' => 1999,
    ]);

    $response->assertSessionHasErrors('titre');
});

// ── EDIT : un admin peut accéder au formulaire d'édition ──
it('permet à un admin de modifier un film', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $film = Film::factory()->create(['titre' => 'Old Title']);

    $response = $this->actingAs($admin)->get("/film/{$film->id}/edit");

    $response->assertStatus(200);
});

// ── EDIT : un utilisateur normal ne peut PAS modifier ──
it('interdit à un utilisateur normal de modifier un film', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $film = Film::factory()->create();

    $response = $this->actingAs($user)->get("/film/{$film->id}/edit");

    $response->assertStatus(403);
});

// ── DESTROY : un admin peut supprimer un film ──
it('permet à un admin de supprimer un film', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $film = Film::factory()->create();

    $response = $this->actingAs($admin)->delete("/film/{$film->id}");

    $response->assertRedirect('/film');
    $this->assertDatabaseMissing('films', ['id' => $film->id]);
});
