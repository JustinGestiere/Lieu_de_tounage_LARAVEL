<?php

namespace App\Models;

use Database\Factories\FilmFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Film extends Model
{
    /** @use HasFactory<FilmFactory> */
    use HasFactory;

    protected $fillable = [
        'titre',
        'annee',
        'synopsis',
    ];

    public $timestamps = false;

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    protected function casts(): array
    {
        return [
            'titre' => 'string',
            'annee' => 'integer',
            'synopsis' => 'string',
        ];
    }
}
