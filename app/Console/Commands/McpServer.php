<?php

namespace App\Console\Commands;

use App\Models\Film;
use Illuminate\Console\Command;

/**
 * Serveur MCP (Model Context Protocol) en lecture seule.
 *
 * ═══════════════════════════════════════════════════════════════
 * QU'EST-CE QUE LE MCP ?
 * ═══════════════════════════════════════════════════════════════
 * Le MCP (Model Context Protocol) est un protocole standardisé
 * qui permet aux IA (comme Claude, ChatGPT, Copilot, etc.)
 * de communiquer avec des applications externes pour lire
 * ou écrire des données. C'est comme une API, mais conçue
 * spécifiquement pour les agents IA.
 *
 * ═══════════════════════════════════════════════════════════════
 * COMMENT ÇA MARCHE ?
 * ═══════════════════════════════════════════════════════════════
 * 1. Le client IA lance la commande : php artisan mcp:serve
 * 2. Le serveur écoute sur stdin (entrée standard)
 * 3. Le client envoie des requêtes JSON-RPC (une par ligne)
 * 4. Le serveur répond sur stdout (sortie standard)
 *
 * C'est le mode "stdio" : pas besoin de serveur HTTP,
 * l'IA et notre serveur communiquent via des tubes (pipes).
 *
 * ═══════════════════════════════════════════════════════════════
 * FLUX DE COMMUNICATION (dans l'ordre) :
 * ═══════════════════════════════════════════════════════════════
 *   Client IA ──► initialize           (présentation mutuelle)
 *   Serveur   ◄── réponse initialize   (capacités du serveur)
 *   Client IA ──► notifications/initialized (confirmation)
 *   Client IA ──► tools/list           (quels outils as-tu ?)
 *   Serveur   ◄── liste des outils     (list_films, get_locations_for_film)
 *   Client IA ──► tools/call           (appelle un outil)
 *   Serveur   ◄── résultat de l'outil  (données JSON)
 *
 * ═══════════════════════════════════════════════════════════════
 * OUTILS DISPONIBLES (lecture seule) :
 * ═══════════════════════════════════════════════════════════════
 *   - list_films              → Liste tous les films (id, titre, année)
 *   - get_locations_for_film  → Lieux de tournage d'un film (par film_id)
 *
 * ═══════════════════════════════════════════════════════════════
 * CONFIGURATION CLIENT (ex: Claude Desktop) :
 * ═══════════════════════════════════════════════════════════════
 *   {
 *     "mcpServers": {
 *       "laravel-films": {
 *         "command": "php",
 *         "args": ["artisan", "mcp:serve"],
 *         "cwd": "C:/xampp/htdocs/b3/Laravel_nathan_camille_justin"
 *       }
 *     }
 *   }
 */
class McpServer extends Command
{
    /**
     * Nom de la commande Artisan.
     * On l'appelle avec : php artisan mcp:serve
     */
    protected $signature = 'mcp:serve';

    /**
     * Description affichée dans la liste des commandes (php artisan list).
     */
    protected $description = 'Démarre un serveur Model Context Protocol (MCP) en lecture seule via stdio';

    /**
     * Point d'entrée de la commande.
     *
     * C'est une boucle infinie qui :
     * 1. Lit une ligne JSON depuis stdin
     * 2. La décode et la traite
     * 3. Écrit la réponse JSON sur stdout
     *
     * La boucle s'arrête quand le client ferme la connexion
     * (fgets retourne false quand stdin est fermé).
     */
    public function handle()
    {
        // Ouvre l'entrée standard en lecture
        // C'est par ici que le client MCP (l'IA) envoie ses requêtes
        $stdin = fopen('php://stdin', 'r');

        // Boucle principale : on lit ligne par ligne
        while ($line = fgets($stdin)) {

            // Décode le JSON reçu en tableau associatif PHP
            $request = json_decode($line, true);

            // Si le JSON est invalide ou n'est pas du JSON-RPC, on ignore
            if (!$request || !isset($request['jsonrpc'])) {
                continue;
            }

            try {
                // Traite la requête et obtient la réponse
                $response = $this->handleRequest($request);

                // Les notifications (sans id) ne nécessitent pas de réponse
                // donc $response peut être null
                if ($response) {
                    // Écrit la réponse JSON sur stdout (sortie standard)
                    // Le client MCP lit cette ligne pour obtenir le résultat
                    fwrite(STDOUT, json_encode($response)."\n");
                }
            } catch (\Exception $e) {
                // En cas d'erreur inattendue, on renvoie une erreur JSON-RPC
                $id = $request['id'] ?? null;
                if ($id !== null) {
                    $errorResponse = [
                        'jsonrpc' => '2.0',
                        'id' => $id,
                        'error' => [
                            'code' => -32000,           // Code d'erreur serveur générique
                            'message' => $e->getMessage(),
                        ],
                    ];
                    fwrite(STDOUT, json_encode($errorResponse)."\n");
                }
            }
        }
    }

    /**
     * Aiguillage principal : selon la méthode demandée,
     * on appelle le bon traitement.
     *
     * Le protocole MCP utilise JSON-RPC 2.0, qui fonctionne ainsi :
     * - Chaque requête a un champ "method" (ex: "initialize", "tools/list")
     * - Les requêtes avec un "id" attendent une réponse
     * - Les notifications (sans "id") n'attendent pas de réponse → on retourne null
     *
     * @param  array  $request  La requête JSON-RPC décodée
     * @return array|null       La réponse à envoyer, ou null si c'est une notification
     */
    private function handleRequest(array $request): ?array
    {
        $method = $request['method'] ?? '';
        $id = $request['id'] ?? null;

        // ┌─────────────────────────────────────────────────┐
        // │  ÉTAPE 1 : INITIALISATION (handshake)           │
        // │  Le client se présente, le serveur répond       │
        // │  avec ses capacités et ses informations.        │
        // └─────────────────────────────────────────────────┘
        if ($method === 'initialize') {
            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'result' => [
                    'protocolVersion' => '2024-11-05',   // Version du protocole MCP supportée
                    'capabilities' => [
                        'tools' => [],                    // On déclare qu'on supporte les "tools"
                    ],
                    'serverInfo' => [
                        'name' => 'laravel-mcp',         // Nom de notre serveur
                        'version' => '1.0.0',
                    ],
                ],
            ];
        }

        // ┌─────────────────────────────────────────────────┐
        // │  ÉTAPE 2 : CONFIRMATION D'INITIALISATION        │
        // │  C'est une notification (pas d'id) : le client  │
        // │  confirme qu'il a bien reçu l'initialisation.   │
        // │  On ne répond rien (return null).                │
        // └─────────────────────────────────────────────────┘
        if ($method === 'notifications/initialized') {
            return null;
        }

        // ┌─────────────────────────────────────────────────┐
        // │  ÉTAPE 3 : LISTE DES OUTILS DISPONIBLES         │
        // │  Le client demande "quels outils proposes-tu ?" │
        // │  On décrit chaque outil avec son nom, sa        │
        // │  description et le schéma de ses paramètres.    │
        // └─────────────────────────────────────────────────┘
        if ($method === 'tools/list') {
            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'result' => [
                    'tools' => [

                        // ── Outil 1 : list_films ──
                        // Aucun paramètre requis, retourne tous les films
                        [
                            'name' => 'list_films',
                            'description' => 'Liste les films disponibles (titre, annee, id)',
                            'inputSchema' => [
                                'type' => 'object',
                                'properties' => (object) [],   // Pas de paramètres → objet vide
                            ],
                        ],

                        // ── Outil 2 : get_locations_for_film ──
                        // Nécessite un paramètre film_id (obligatoire)
                        [
                            'name' => 'get_locations_for_film',
                            'description' => 'Recupere les lieux de tournage d\'un film specifique',
                            'inputSchema' => [
                                'type' => 'object',
                                'properties' => [
                                    'film_id' => [
                                        'type' => 'number',
                                        'description' => 'ID du film',
                                    ],
                                ],
                                'required' => ['film_id'],    // Ce paramètre est obligatoire
                            ],
                        ],
                    ],
                ],
            ];
        }

        // ┌─────────────────────────────────────────────────┐
        // │  ÉTAPE 4 : APPEL D'UN OUTIL                     │
        // │  Le client demande d'exécuter un outil précis.  │
        // │  On lit le nom de l'outil et ses arguments,     │
        // │  puis on interroge la BDD via Eloquent.         │
        // └─────────────────────────────────────────────────┘
        if ($method === 'tools/call') {
            $toolName = $request['params']['name'] ?? '';       // Nom de l'outil demandé
            $args = $request['params']['arguments'] ?? [];     // Arguments passés par l'IA

            // ── OUTIL : list_films ──
            // Requête Eloquent simple : on récupère id, titre, année de tous les films
            if ($toolName === 'list_films') {
                $films = Film::select('id', 'titre', 'annee')->get();
                return [
                    'jsonrpc' => '2.0',
                    'id' => $id,
                    'result' => [
                        'content' => [
                            [
                                'type' => 'text',
                                // On convertit la collection Eloquent en JSON lisible
                                'text' => $films->toJson(JSON_PRETTY_PRINT),
                            ],
                        ],
                    ],
                ];
            }

            // ── OUTIL : get_locations_for_film ──
            // On charge le film avec ses locations (eager loading)
            if ($toolName === 'get_locations_for_film') {
                $filmId = $args['film_id'] ?? null;

                // Validation : film_id est obligatoire
                if (!$filmId) {
                    return [
                        'jsonrpc' => '2.0',
                        'id' => $id,
                        'error' => [
                            'code' => -32602,       // Code JSON-RPC : paramètres invalides
                            'message' => 'Invalid params: film_id is required',
                        ],
                    ];
                }

                // Recherche du film avec ses locations via Eloquent
                // Film::with('locations') fait un eager loading pour éviter le N+1
                $film = Film::with('locations')->find($filmId);

                // Si le film n'existe pas, on retourne une erreur "douce"
                // (isError: true dans le résultat, pas dans error)
                if (!$film) {
                    return [
                        'jsonrpc' => '2.0',
                        'id' => $id,
                        'result' => [
                            'isError' => true,
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => "Film non trouve avec l'id: {$filmId}",
                                ],
                            ],
                        ],
                    ];
                }

                // Succès : on retourne le film et ses lieux de tournage
                return [
                    'jsonrpc' => '2.0',
                    'id' => $id,
                    'result' => [
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => json_encode([
                                    'film' => $film->titre,
                                    'locations' => $film->locations->map(function ($loc) {
                                        // On ne renvoie que les champs utiles (lecture seule)
                                        return [
                                            'name' => $loc->name,
                                            'city' => $loc->city,
                                            'country' => $loc->country,
                                        ];
                                    })
                                ], JSON_PRETTY_PRINT),
                            ],
                        ],
                    ],
                ];
            }

            // Si l'outil demandé n'existe pas → erreur JSON-RPC
            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'error' => [
                    'code' => -32601,
                    'message' => "Tool not found: {$toolName}",
                ],
            ];
        }

        // ┌─────────────────────────────────────────────────┐
        // │  FALLBACK : MÉTHODE INCONNUE                    │
        // │  Si la méthode n'est ni initialize, ni          │
        // │  tools/list, ni tools/call → erreur.            │
        // └─────────────────────────────────────────────────┘
        if ($id !== null) {
            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'error' => [
                    'code' => -32601,       // Code JSON-RPC : méthode non trouvée
                    'message' => 'Method not found',
                ],
            ];
        }

        // Notification inconnue → on ignore silencieusement
        return null;
    }
}
