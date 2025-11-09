<?php

namespace App\Services;

use DB;
use App\Models\Pokemon;
use App\Models\Type;
use App\Models\PokemonType;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Command;

class PokemonScanService
{
    private ?Command $command = null;

    public function setCommand(?Command $command): void
    {
        $this->command = $command;
    }
    private function getBaseUrl(): string
    {
        $baseUrl = config('app.api_base_url');

        if (empty($baseUrl)) {
            throw new \Exception("POKEAPI_BASE_URL is not set in configuration.");
        }

        return $baseUrl;
    }

    private function info(string $message): void
    {
        if ($this->command) {
            $this->command->info($message);
        }
    }

    /**
     * Fetch and store Pokemon names from PokeAPI
     *
     * @param int $limit Number of Pokemon to fetch per request
     * @param int $offset Starting offset for fetching Pokemon
     * @return array ['fetched' => int, 'errors' => int, 'total_from_api' => int]
     */
    public function fetchPokemonNames(int $limit = 200, int $offset = 0): array
    {
        $baseUrl = $this->getBaseUrl();
        $currentOffset = $offset;
        $fetched = 0;
        $errors = 0;
        if (is_null($baseUrl) || empty($baseUrl)) {
            throw new \Exception("POKEAPI_BASE_URL is not set in environment variables.");
        }
        $totalFromDB = Pokemon::count();

        $response = file_get_contents("{$baseUrl}/pokemon?limit=1");
        $data = json_decode($response, true);
        $totalFromAPI = $data['count'];
        $this->info("Total Pokemon in PokeAPI: {$totalFromAPI}, Total in DB: {$totalFromDB}");

        if ($totalFromAPI <= $totalFromDB) {
            $this->info("No new Pokemon to fetch. Total in DB: {$totalFromDB}, Total in PokeAPI: {$totalFromAPI}");
            return [
                'fetched' => 0,
                'errors' => 0,
                'total_from_api' => $totalFromAPI,
                'total_from_db' => $totalFromDB,
                'up_to_date' => true
            ];
        }


        while ($currentOffset < $totalFromAPI) {
            $this->info("Fetching Pokemon names with limit={$limit}, offset={$currentOffset}");
            $response = file_get_contents("{$baseUrl}/pokemon?limit={$limit}&offset={$currentOffset}");
            $data = json_decode($response, true);

            $batchSize = count($data['results']);
            $fetched += $batchSize;

            foreach ($data['results'] as $pokemonData) {
                DB::beginTransaction();
                try {
                    Pokemon::updateOrCreate(
                        ['name' => $pokemonData['name']],
                        ['name' => $pokemonData['name']]
                    );
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $errors++;
                    continue;
                }
            }
            $this->info("Fetched {$batchSize} Pokemon names in this batch.");
            $currentOffset += $limit;
            usleep(100000);
        }

        return [
            'fetched' => $fetched,
            'errors' => $errors,
            'total_from_api' => $totalFromAPI,
            'total_from_db' => $totalFromDB,
            'up_to_date' => false
        ];
    }

    /**
     * Fetch and update Pokemon details (height, weight, types) from PokeAPI
     *
     * @param int $limit Number of Pokemon to process per batch
     * @return array ['processed' => int, 'errors' => int, 'remaining' => int]
     */
    public function fetchPokemonDetails(int $limit = 200): array
    {

        $baseUrl = $this->getBaseUrl();

        $errors = 0;

        $missingDetailsCount = Pokemon::whereNull('height')->orWhereNull('weight')->orWhereNull('poke_api_id')->count();
        $this->info("Total Pokemon with missing details: {$missingDetailsCount}");
        if ($missingDetailsCount == 0) {
            $this->info("All Pokemon details are up to date.");
            return [
                'processed' => 0,
                'errors' => 0,
                'remaining' => 0,
                'all_updated' => true
            ];
        }
        $pokemonsWithMissingDetails = Pokemon::whereNull('height')->orWhereNull('weight')->orWhereNull('poke_api_id')->limit($limit)->get();
        $processed = 0;

        foreach ($pokemonsWithMissingDetails as $pokemon) {
            $this->info("Fetching details for Pokemon: {$pokemon->name}");
            DB::beginTransaction();
            try {
                $response = file_get_contents("{$baseUrl}/pokemon/{$pokemon->name}");
                $details = json_decode($response, true);
                $pokemon->height = $details['height'];
                $pokemon->weight = $details['weight'];
                $pokemon->poke_api_id = $details['id'];
                $pokemon->save();

                foreach ($details['types'] as $typeInfo) {
                    $typeName = $typeInfo['type']['name'];
                    $type = Type::firstOrCreate(['name' => $typeName]);
                    $pokemonType = $pokemon->pokemonTypes()->where('type_id', $type->id)->first();
                    if (!$pokemonType) {
                        PokemonType::create([
                            'pokemon_id' => $pokemon->id,
                            'type_id' => $type->id,
                        ]);
                    }
                    $pokemon->save();
                }
                DB::commit();
                $processed++;
            } catch (\Exception $e) {
                DB::rollBack();
                $errors++;
                continue;
            }
            $this->info("Fetched details for Pokemon: {$pokemon->name} \n");
            sleep(1);
        }

        $remainingCount = Pokemon::whereNull('height')->orWhereNull('weight')->orWhereNull('poke_api_id')->count();

        return [
            'processed' => $processed,
            'errors' => $errors,
            'remaining' => $remainingCount,
            'all_updated' => false
        ];
    }

    public function getMissingDetailsCount(): int
    {
        return Pokemon::whereNull('height')->orWhereNull('weight')->orWhereNull('poke_api_id')->count();
    }
}
