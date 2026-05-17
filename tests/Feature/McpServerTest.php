<?php

use App\Console\Commands\McpServer;
use App\Models\Film;
use App\Models\Location;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Tests Feature : Serveur MCP
|--------------------------------------------------------------------------
|
| Ces tests vérifient que la commande Artisan mcp:serve répond
| correctement aux requêtes JSON-RPC du protocole MCP.
|
| Comme le serveur lit depuis stdin, on simule l'entrée via
| des appels directs à la méthode handleRequest (via Reflection)
| plutôt que de lancer un vrai processus.
|
*/

// Helper pour appeler la méthode privée handleRequest
function callMcpHandler(array $request): ?array
{
    $command = new McpServer();
    $reflection = new ReflectionMethod($command, 'handleRequest');
    $reflection->setAccessible(true);

    return $reflection->invoke($command, $request);
}

// ── INITIALIZE : le serveur se présente correctement ──
it('répond à la requête initialize', function () {
    $response = callMcpHandler([
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'initialize',
        'params' => [],
    ]);

    expect($response)->not->toBeNull()
        ->and($response['result']['protocolVersion'])->toBe('2024-11-05')
        ->and($response['result']['serverInfo']['name'])->toBe('laravel-mcp');
});

// ── TOOLS/LIST : expose les 2 outils attendus ──
it('liste les outils MCP disponibles', function () {
    $response = callMcpHandler([
        'jsonrpc' => '2.0',
        'id' => 2,
        'method' => 'tools/list',
        'params' => [],
    ]);

    $toolNames = array_column($response['result']['tools'], 'name');

    expect($toolNames)->toContain('list_films')
        ->and($toolNames)->toContain('get_locations_for_film');
});

// ── TOOLS/CALL list_films : retourne les films en JSON ──
it('retourne la liste des films via list_films', function () {
    Film::factory()->create(['titre' => 'Inception', 'annee' => 2010]);
    Film::factory()->create(['titre' => 'Matrix', 'annee' => 1999]);

    $response = callMcpHandler([
        'jsonrpc' => '2.0',
        'id' => 3,
        'method' => 'tools/call',
        'params' => [
            'name' => 'list_films',
            'arguments' => [],
        ],
    ]);

    $text = $response['result']['content'][0]['text'];
    $films = json_decode($text, true);

    expect($films)->toHaveCount(2)
        ->and($text)->toContain('Inception')
        ->and($text)->toContain('Matrix');
});

// ── TOOLS/CALL get_locations_for_film : retourne les lieux ──
it('retourne les locations d\'un film via get_locations_for_film', function () {
    $user = User::factory()->create();
    $film = Film::factory()->create(['titre' => 'Batman']);
    Location::factory()->create([
        'film_id' => $film->id,
        'user_id' => $user->id,
        'name' => 'Gotham City Set',
        'city' => 'Chicago',
        'country' => 'USA',
    ]);

    $response = callMcpHandler([
        'jsonrpc' => '2.0',
        'id' => 4,
        'method' => 'tools/call',
        'params' => [
            'name' => 'get_locations_for_film',
            'arguments' => ['film_id' => $film->id],
        ],
    ]);

    $text = $response['result']['content'][0]['text'];

    expect($text)->toContain('Gotham City Set')
        ->and($text)->toContain('Chicago')
        ->and($text)->toContain('Batman');
});

// ── TOOLS/CALL get_locations_for_film : film inexistant ──
it('retourne une erreur douce pour un film inexistant', function () {
    $response = callMcpHandler([
        'jsonrpc' => '2.0',
        'id' => 5,
        'method' => 'tools/call',
        'params' => [
            'name' => 'get_locations_for_film',
            'arguments' => ['film_id' => 99999],
        ],
    ]);

    expect($response['result']['isError'])->toBeTrue()
        ->and($response['result']['content'][0]['text'])->toContain('Film non trouve');
});

// ── TOOLS/CALL get_locations_for_film : sans film_id ──
it('retourne une erreur si film_id est manquant', function () {
    $response = callMcpHandler([
        'jsonrpc' => '2.0',
        'id' => 6,
        'method' => 'tools/call',
        'params' => [
            'name' => 'get_locations_for_film',
            'arguments' => [],
        ],
    ]);

    expect($response['error']['code'])->toBe(-32602);
});

// ── Notifications : pas de réponse ──
it('ne répond rien pour une notification initialized', function () {
    $response = callMcpHandler([
        'jsonrpc' => '2.0',
        'method' => 'notifications/initialized',
    ]);

    expect($response)->toBeNull();
});

// ── Méthode inconnue : erreur JSON-RPC ──
it('retourne une erreur pour une méthode inconnue', function () {
    $response = callMcpHandler([
        'jsonrpc' => '2.0',
        'id' => 7,
        'method' => 'unknown/method',
        'params' => [],
    ]);

    expect($response['error']['code'])->toBe(-32601);
});
