<?php

use App\Http\Controllers\PokemonController_V1;
use Illuminate\Support\Facades\Route;

Route::get('/pokemons', [PokemonController_V1::class, 'getList']);