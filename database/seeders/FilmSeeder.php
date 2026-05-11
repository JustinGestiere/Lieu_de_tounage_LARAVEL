<?php

namespace Database\Seeders;

use App\Models\Film;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FilmSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Film::factory()->create([
            'titre' => 'Test Film',
            'annee' => 2022,
            'synopsis' => 'Description du Test Film',
        ]);
    }
}
