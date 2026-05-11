<?php

namespace App\Models;

use Database\Factories\LocationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    /** @use HasFactory<LocationFactory> */
    use HasFactory;

    protected $fillable = [
        'film_id',
        'user_id',
        'name',
        'city',
        'country',
        'description',
        'upvotes_count',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'film_id' => 'integer',
            'user_id' => 'integer',
            'name' => 'string',
            'city' => 'string',
            'country' => 'string',
            'description' => 'string',
            'upvotes_count' => 'integer',
        ];
    }

    public function film()
    {
        return $this->belongsTo(Film::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
