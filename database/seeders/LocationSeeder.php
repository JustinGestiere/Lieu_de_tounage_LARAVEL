<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Location::factory()->create([
            'film_id' => 1,
            'user_id' => 1,
            'name' => 'Château de Brissac',
            'city' => 'Brissac-Quincé',
            'country' => 'France',
            'description' => 'Lieu du tournage du film Batman',
            'upvotes_count' => 0,
        ]);
    }
}
