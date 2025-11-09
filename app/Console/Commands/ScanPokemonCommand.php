<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PokemonScanService;

class ScanPokemonCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:pokemon
                            {--limit=200 : Number of Pokemon to fetch per request}
                            {--offset=0 : Starting offset for fetching Pokemon}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch Pokemon data from PokeAPI and store in database';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(PokemonScanService $pokemonScanService): int
    {    
        $pokemonScanService->setCommand($this);
        $limit = (int) $this->option('limit');
        $offset = (int) $this->option('offset');

        $this->info("Starting Pokemon scan...");
        $this->info("Fetching Pokemon with limit={$limit}, starting from offset={$offset}");

        try {
            // Fetch Pokemon names
            $result = $pokemonScanService->fetchPokemonNames($limit, $offset);

            $this->info("Total Pokemon in DB: {$result['total_from_db']}, Total Pokemon in PokeAPI: {$result['total_from_api']}");

            if ($result['up_to_date']) {
                $this->info("No new Pokemon to fetch. Total in DB: {$result['total_from_db']}, Total in PokeAPI: {$result['total_from_api']}");
            }

            $this->info("Pokemon name scan completed. Total fetched: {$result['fetched']}, Total errors: {$result['errors']}");

            // Fetch Pokemon details
            $missingDetailsCount = $pokemonScanService->getMissingDetailsCount();
            
            if ($missingDetailsCount == 0) {
                $this->info("All Pokemon details are up to date.");
                return Command::SUCCESS;
            }

            $this->info("Start to scan missing Pokemon details. Remaining: {$missingDetailsCount}");
            
            $detailsResult = $pokemonScanService->fetchPokemonDetails($limit);
            
            $this->info("Completed scanning batch of missing Pokemon details.");
            $this->info("Processed: {$detailsResult['processed']}, Errors: {$detailsResult['errors']}, Remaining: {$detailsResult['remaining']}");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Failed to fetch Pokemon data from PokeAPI: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
