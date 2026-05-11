# Projet Laravel - Gestion de Films et Lieux de Tournage

Ce projet est une application Laravel réalisée dans le cadre d'un TP couvrant l'authentification, les CRUDs métier, les Middlewares personnalisés, les Jobs/Queues et les commandes Artisan planifiées.

## 🚀 Journal de Bord et Fonctionnalités

### Étape 1 : Authentification
**Pourquoi ?** Garantir un accès sécurisé et identifier les utilisateurs.
L'authentification est le premier rempart de l'application. Elle permet de s'assurer que seuls les membres inscrits peuvent accéder aux fonctionnalités et, plus tard, d'associer des données (comme des lieux de tournage) à des auteurs précis.

- **Commande :** `php artisan breeze:install blade`
- **Fichiers clés :** 
  - `routes/auth.php` (définition des routes de login)
  - `app/Models/User.php` (modèle utilisateur de base)
  - `resources/views/layouts/navigation.blade.php` (menue de navigation)
- **Action :** Utilisation de Laravel Breeze pour fournir un système d'authentification robuste (Login, Register, Dashboard).

### Étape 2 : CRUDs Métier (Films & Locations)
**Pourquoi ?** Créer la structure et la logique fondamentale du projet.
Les CRUDs (Create, Read, Update, Delete) constituent le cœur de la plateforme. Ils permettent de manipuler les données essentielles : gérer le catalogue de films et répertorier les lieux de tournage. C'est ici que l'on définit comment les informations circulent et se stockent.

- **Commande :** `php artisan make:model [Film/Location] -a` (Génère Model, Migrations, Controller, Factory, Seeder et Policy).
- **Fichiers clés :** 
  - `app/Models/Film.php` & `app/Models/Location.php`
  - `database/migrations/..._create_films_table.php` & `database/migrations/..._create_locations_table.php`
  - `app/Http/Controllers/FilmController.php` & `app/Http/Controllers/LocationController.php`
  - `database/factories/FilmFactory.php` & `database/factories/LocationFactory.php`
  - `database/seeders/FilmSeeder.php` & `database/seeders/LocationSeeder.php`
  - `resources/views/film/*.blade.php` & `resources/views/location/*.blade.php` (index, create, edit)
- **Action :** Création des tables et de la relation `BelongsTo`. L'utilisateur choisit un film pour sa location. L'auteur de la location est automatiquement enregistré via `auth()->id()`.

### Étape 3 : Middleware & Droits Admin
**Pourquoi ?** Sécuriser les actions sensibles et hiérarchiser les accès.
Tous les utilisateurs ne doivent pas avoir les mêmes pouvoirs. Le Middleware permet de filtrer les requêtes : un utilisateur classique peut suggérer un lieu, mais seul un administrateur peut modifier le catalogue des films ou supprimer des entrées critiques. Cela garantit l'intégrité des données.

- **Commandes :** 
  - `php artisan make:middleware AdminMiddleware`
  - `php artisan make:migration add_is_admin_to_users_table`
- **Fichiers clés :** 
  - `app/Http/Middleware/AdminMiddleware.php`
  - `bootstrap/app.php` (Enregistrement de l'alias `admin`)
  - `routes/web.php` (Définition des groupes de routes protégés)
- **Action :** Ajout d'une colonne `is_admin` aux utilisateurs. Seuls les admins accèdent aux CRUDs Films. Pour les Locations, l'auteur peut modifier son propre poste, mais seul l'admin peut tout modifier/supprimer.

### Étape 4 : Système d'Upvotes (Queues & Jobs)
**Pourquoi ?** Optimiser les performances et la fluidité de l'interface.
Certaines tâches, comme le recalcul massif de statistiques ou de votes, peuvent être lourdes. En utilisant des Jobs et des Files d'attente (Queues), on libère l'utilisateur immédiatement : le serveur traite la demande en arrière-plan sans bloquer l'affichage de la page.

- **Commandes :** 
  - `php artisan make:job UpdateUpvotesCountJob`
  - `php artisan make:migration create_location_votes_table`
- **Fichiers clés :** 
  - `app/Jobs/UpdateUpvotesCountJob.php`
  - `app/Http/Controllers/LocationController.php` (Appel du Job via `dispatch()`)
  - `.env` (Passage de `QUEUE_CONNECTION` à `database`)
- **Action :** Un clic sur "Upvote" enregistre une ligne dans `location_votes` (vote unique garanti par contrainte SQL). Un Job en file d'attente recalcule ensuite le total pour mettre à jour la colonne `upvotes_count`.

### Étape 5 : Commande Artisan & Scheduler
**Pourquoi ?** Automatiser la maintenance et le nettoyage de l'application.
L'automatisation permet de maintenir une base de données saine sans intervention humaine. On définit des règles de nettoyage (par exemple supprimer les posts obsolètes) pour éviter l'accumulation de données inutiles.
- **Commande :** `php artisan make:command CleanOldLocations`
- **Fichiers clés :** 
  - `app/Console/Commands/CleanOldLocations.php` (Logique de suppression)
  - `routes/console.php` (Planification via `Schedule::command()->daily()`)
- **Action :** Commande `locations:clean` qui supprime les lieux de + de 14 jours avec moins de 2 votes.

### Étape 6 : Qualité de Code (Laravel Pint)
**Pourquoi ?** Garantir une base de code uniforme et lisible.
Laravel Pint est un outil de formatage (linter) qui s'assure que tout le code PHP respecte les mêmes conventions de style. Cela uniformise l'écriture du code entre les différents développeurs et permet de repérer rapidement les erreurs de syntaxe ou les importations inutiles.
- **Commande :** `./vendor/bin/pint`
- **Fichiers clés :**
  - Tous les fichiers PHP du projet (Formatage automatique)
- **Action :** Formatage automatique des fichiers PHP pour respecter le standard Laravel/PSR.

### Étape 7 : Connexion via Réseaux Sociaux (OAuth)
**Pourquoi ?** Simplifier l'expérience utilisateur et augmenter le taux d'inscription.
L'ajout d'une connexion via un compte tiers (GitHub ici) évite à l'utilisateur de devoir créer et mémoriser un nouveau mot de passe. OAuth permet de récupérer de manière sécurisée les informations de l'utilisateur (nom, email) pour créer automatiquement son compte.
- **Commande :**
   ```bash
   composer require laravel/socialite
   ```
- **Fichiers clés :** 
  - `config/services.php` (Configuration du client ID/Secret)
  - `routes/auth.php` (Routes de redirection et de callback)
  - `resources/views/auth/login.blade.php` (Bouton de connexion GitHub)
- **Action :** L'utilisateur clique sur le bouton, est redirigé vers GitHub pour autoriser l'application, puis est reconnecté automatiquement sur le Dashboard Laravel.

### Étape 8 : Abonnement Stripe + Route API JSON
**Pourquoi ?** Monétiser l'accès à des données via une API sécurisée.
Stripe permet de gérer des paiements en mode test, tandis que JWT sécurise l'accès à l'API. Seuls les utilisateurs abonnés et authentifiés peuvent accéder aux données des films via l'API.

- **Commandes :**
  ```bash
  composer require stripe/stripe-php
  composer require tymon/jwt-auth
  php artisan config:clear
  ```
- **Fichiers clés :**
  - `composer.json` (Ajout stripe/stripe-php)
  - `config/auth.php` (Ajout guard api avec driver JWT)
  - `config/services.php` (Ajout config Stripe key/secret)
  - `routes/web.php` (Nommer route subscribe, ajouter routes Stripe checkout/success/cancel)
  - `routes/api.php` (Route /api/films/{film}/locations avec auth:api + subscribed)
  - `app/Http/Controllers/StripeController.php` (Créé controller Stripe avec checkout session)
  - `app/Http/Middleware/IsSubscribed.php` (Modifié pour gérer web et API)
  - `resources/views/layouts/navigation.blade.php` (Ajout bouton paiement desktop)
  - `.env` (JWT_SECRET et STRIPE_SECRET configurés)
- **Action :** L'utilisateur clique sur "Payer 5€/mois", est redirigé vers Stripe Checkout (carte test 4242...), puis `is_subscribed` passe à true. L'API `/api/films/{id}/locations` nécessite un token JWT valide + abonnement actif.

### Étape 9 : MCP simple pour les IA
**Pourquoi ?** Permettre à des intelligences artificielles ou des agents d'interroger facilement la base de données de l'application.
Le Model Context Protocol (MCP) standardise la façon dont les modèles d'IA interagissent avec les sources de données. Nous avons créé un serveur MCP local qui expose les données en lecture seule.

- **Commande :** `php artisan make:command McpServer`
- **Fichiers clés :**
  - `app/Console/Commands/McpServer.php` (Logique de communication via stdio en JSON-RPC)
- **Action :** Implémentation d'un serveur MCP natif en PHP écoutant sur les entrées/sorties standard (stdio). Le serveur fournit deux outils (`list_films` et `get_locations_for_film`) qui requêtent directement les modèles Eloquent (`Film` et `Location`) de façon sécurisée (lecture seule). Un client MCP (comme Claude Desktop ou un IDE compatible) peut utiliser la commande `php artisan mcp:serve` pour interroger facilement le contexte de l'application.

### Étape 10 : CI/CD, Tests & Docker
**Pourquoi ?** Automatiser la validation du code et permettre un déploiement reproductible sur un VPS.
La CI/CD garantit que chaque push est testé automatiquement. Docker permet de déployer l'application de manière identique sur n'importe quel serveur, avec PostgreSQL comme base de données de production.

- **Fichiers clés :**
  - `.github/workflows/ci.yml` (Pipeline GitHub Actions : tests, lint, build Docker)
  - `Dockerfile` (Image multi-stage : Node pour les assets + PHP 8.3 Apache)
  - `docker-compose.yml` (Orchestration Laravel + PostgreSQL 16)
  - `docker-entrypoint.sh` (Script de démarrage : migrations + cache)
  - `.dockerignore` (Exclut les fichiers inutiles de l'image Docker)
  - `tests/Feature/FilmTest.php` (Tests CRUD Films : 8 tests)
  - `tests/Feature/LocationTest.php` (Tests CRUD Locations + Upvotes : 7 tests)
  - `tests/Feature/McpServerTest.php` (Tests du serveur MCP : 7 tests)
- **Action :** À chaque push sur `main`, GitHub Actions lance 3 jobs :
  1. **Tests** — Pest avec SQLite in-memory (rapide, pas besoin de PostgreSQL en CI)
  2. **Lint** — Laravel Pint vérifie le formatage du code
  3. **Docker** — Build de l'image pour valider le Dockerfile (ne passe que si tests + lint sont OK)

---

## 🛠 Procédure de Lancement rapide (Local)

1. **Initialisation :**
   ```bash
   composer install
   npm install && npm run dev
   cp .env.example .env
   php artisan key:generate
   # Ajouter GITHUB_CLIENT_ID, GITHUB_CLIENT_SECRET et GITHUB_REDIRECT dans le .env
   # Ajouter STRIPE_KEY et STRIPE_SECRET dans le .env
   ```

2. **Base de données (SQLite en local) :**
   ```bash
   php artisan migrate:fresh --seed
   ```

3. **Passer Admin (via Tinker) :**
   ```bash
   php artisan tinker
   # App\Models\User::first()->update(['is_admin' => true]);
   ```

4. **Lancer les Queues :**
   ```bash
   php artisan queue:work
   ```

5. **Formater le code (Pint) :**
   ```bash
   ./vendor/bin/pint
   ```

6. **Lancer les tests :**
   ```bash
   php artisan test
   ```

---

## 🐳 Déploiement Docker (VPS avec PostgreSQL)

### Pourquoi PostgreSQL ?
Oui, **une base de données est nécessaire**. L'application utilise des migrations, des relations Eloquent (Films → Locations), des tables de votes, des sessions et des jobs en file d'attente. SQLite convient pour le développement local, mais en production sur un VPS, PostgreSQL est le choix recommandé car :
- Il gère les accès concurrents (plusieurs utilisateurs simultanés)
- Il est plus robuste pour la persistance des données
- Il est gratuit et léger en ressources

### Lancement en une commande :
```bash
docker compose up -d --build
```

Cette commande :
1. **Build** l'image Laravel (PHP 8.3 + Apache + assets Vite compilés)
2. **Démarre** PostgreSQL 16 avec un healthcheck
3. **Attend** que PostgreSQL soit prêt avant de lancer l'app
4. **Exécute automatiquement** les migrations au démarrage du conteneur
5. L'application est accessible sur `http://votre-ip:80`

### Variables d'environnement importantes :
Les variables sont configurées dans `docker-compose.yml`. Pour personnaliser :
```yaml
environment:
  APP_URL: http://votre-domaine.com     # Votre URL publique
  DB_PASSWORD: votre_mot_de_passe       # Changer le mot de passe par défaut
  STRIPE_KEY: pk_live_...               # Clés Stripe production
  STRIPE_SECRET: sk_live_...
  GITHUB_CLIENT_ID: ...                 # OAuth GitHub
  GITHUB_CLIENT_SECRET: ...
  JWT_SECRET: ...                       # Générer avec : php artisan jwt:secret
```

### Commandes utiles :
```bash
# Voir les logs en temps réel
docker compose logs -f app

# Exécuter une commande Artisan dans le conteneur
docker compose exec app php artisan tinker

# Relancer les migrations
docker compose exec app php artisan migrate:fresh --seed

# Arrêter tout
docker compose down

# Arrêter et SUPPRIMER les données PostgreSQL
docker compose down -v
```

---

## 🧪 Tests

Les tests utilisent **Pest** (framework de test pour PHP/Laravel) avec **SQLite en mémoire** pour la rapidité.

### Lancer les tests :
```bash
php artisan test
```

### Tests disponibles (22 tests) :

| Fichier | Tests | Ce qui est vérifié |
|---------|-------|--------------------|
| `FilmTest.php` | 8 | CRUD films, auth obligatoire, validation, droits admin |
| `LocationTest.php` | 7 | CRUD locations, permissions auteur/admin, upvotes, anti-doublon |
| `McpServerTest.php` | 7 | Handshake MCP, liste des outils, appels list_films et get_locations_for_film, gestion d'erreurs |

### Pourquoi SQLite en mémoire pour les tests ?
- **Rapide** : pas de serveur de BDD à démarrer
- **Isolé** : chaque test repart d'une base vierge (RefreshDatabase)
- **Compatible** : les migrations Laravel fonctionnent sur SQLite comme sur PostgreSQL
- **CI-friendly** : pas besoin de configurer PostgreSQL dans GitHub Actions

---

## 🤖 Configuration MCP (pour les IA)

Pour connecter une IA (Claude Desktop, Cursor, etc.) à votre application :
```json
{
  "mcpServers": {
    "laravel-films": {
      "command": "php",
      "args": ["artisan", "mcp:serve"],
      "cwd": "/chemin/vers/Laravel_nathan_camille_justin"
    }
  }
}
```
