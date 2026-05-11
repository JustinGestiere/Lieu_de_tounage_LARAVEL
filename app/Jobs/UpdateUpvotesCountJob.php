<?php

namespace App\Jobs;

use App\Models\Location;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateUpvotesCountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $locationId) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Exécution du Job UpdateUpvotesCountJob pour la location ID: '.$this->locationId);

        // 1. Compter le nombre réel de votes dans la table location_votes
        $realCount = DB::table('location_votes')
            ->where('location_id', $this->locationId)
            ->count();

        // 2. Mettre à jour la colonne upvotes_count dans la table locations
        Location::where('id', $this->locationId)
            ->update(['upvotes_count' => $realCount]);
    }
}
