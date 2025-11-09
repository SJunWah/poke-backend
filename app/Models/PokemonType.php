<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PokemonType extends Model
{
    use HasFactory;

    protected $table = 'pokemon_types';

    protected $fillable = [
        'name',
        'pokemon_id',
        'type_id',
    ];

    /**
     * Get the Pokemon that owns the type.
     */
    public function pokemon(): BelongsTo
    {
        return $this->belongsTo(Pokemon::class);
    }

    /**
     * Get the type that belongs to the PokemonType.
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }
}
