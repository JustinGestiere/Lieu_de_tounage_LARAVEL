<?php

namespace App\Console\Commands;

use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanOldLocations extends Command
{
    /**
     * Le nom et la signature de la commande (ce que vous tapez dans le terminal).
     */
    protected $signature = 'locations:clean';

    /**
     * La description de la commande.
     */
    protected $description = 'Supprime les locations créées il y a plus de 14 jours avec moins de 2 upvotes';

    /**
     * Exécute la commande.
     */
    public function handle()
    {
        $dateLimit = Carbon::now()->subDays(14);

        // Sélection et suppression selon la règle métier
        $deletedCount = Location::where('created_at', '<', $dateLimit)
            ->where('upvotes_count', '<', 2)
            ->delete();

        $this->info("Nettoyage terminé : $deletedCount locations supprimées.");
    }
}
