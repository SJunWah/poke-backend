<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pokemon extends Model
{
    use HasFactory;

    protected $table = 'pokemons';

    protected $fillable = [
        'name',
        'height',
        'weight',
        'poke_api_id',
    ];

    protected $casts = [
        'height' => 'integer',
        'weight' => 'integer',
        'poke_api_id' => 'integer',
    ];

    protected $appends = ['types', 'image'];

    protected $hidden = ['pokemonTypes', 'typeRelations', 'id', 'created_at', 'updated_at', 'poke_api_id'];

    public function pokemonTypes(): HasMany
    {
        return $this->hasMany(PokemonType::class);
    }

    public function typeRelations(): BelongsToMany
    {
        return $this->belongsToMany(
            Type::class,
            'pokemon_types',
            'pokemon_id',
            'type_id'
        );
    }
    public function getImageAttribute(): ?string
    {
        if (!$this->poke_api_id) {
            return null;
        }
        return config('app.sprite_base_url') . "/{$this->poke_api_id}.png";
    }

    public function getTypesAttribute(): array
    {
        return $this->typeRelations->pluck('name')->toArray();
    }

    public static function getList(int $page, int $limit, string $sort, string $order, string|null $name): array
    {
        $offset = ((int) $page - 1) * (int) $limit;

        $query = self::query();
        if ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        }

        $query->orderBy($sort, $order);

        // paginate
        $count = $query->count();
        $totalPage = ceil($count / (int) $limit);
        $offset = ((int) $page - 1) * (int) $limit;
        $result = $query->with('pokemonTypes')->offset($offset)->limit($limit)->get()->toArray();

        return [
            'result' => $result,
            'pagination' => [
                'total' => $count,
                'total_page' => $totalPage,
            ]
        ];
    }


}
