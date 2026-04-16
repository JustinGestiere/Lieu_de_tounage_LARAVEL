<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Film extends Model
{
    /** @use HasFactory<\Database\Factories\FilmFactory> */
    use HasFactory;
    protected $fillable = [
        'titre',
        'annee',
        'synopsis',
    ];

    public $timestamps = false;
    protected function casts(): array
    {
        return [
            'titre' => 'string',
            'annee' => 'integer',
            'synopsis' => 'string',
        ];
    }
}
