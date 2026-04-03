<?php

namespace Oluokunkabiru\AutoSeeder\Commands;

use Illuminate\Console\Command;
use Oluokunkabiru\AutoSeeder\AutoSeeder;
use Throwable;

class SeedAutoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:auto
        {model            : Model class name (short e.g. User) or FQCN (e.g. App\\Models\\Order)}
        {count=1          : Number of rows to generate (default: 1)}
        {--locale=        : Faker locale override (e.g. fr_FR)}
        {--domain=        : Email domain override (e.g. acme.com)}
        {--country-code=  : Phone country code override (e.g. +234)}
        {--skip=          : Comma-separated extra columns to skip}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-seed a model table with randomly generated data based on its database columns';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $modelArg = $this->argument('model');
        $count    = max(1, (int) $this->argument('count'));

        // Resolve FQCN
        $fqcn = $this->resolveModel($modelArg);
        if (!$fqcn) {
            $this->error("Model class not found: {$modelArg}");
            $this->line('  Tried: ' . implode(', ', $this->modelCandidates($modelArg)));
            return self::FAILURE;
        }

        $this->info("Auto-seeding <comment>{$fqcn}</comment> — <comment>{$count}</comment> row(s)...");

        try {
            $seeder = AutoSeeder::fromModel(new $fqcn());

            // Apply optional overrides
            $configure = [];
            if ($domain = $this->option('domain')) {
                $configure['email'] = ['domain' => $domain];
            }
            if ($countryCode = $this->option('country-code')) {
                $configure['phone']  = ['country_code' => $countryCode];
                $configure['mobile'] = ['country_code' => $countryCode];
            }
            if (!empty($configure)) {
                $seeder->configure($configure);
            }
            if ($skip = $this->option('skip')) {
                $seeder->skip(array_map('trim', explode(',', $skip)));
            }

            $bar = $this->output->createProgressBar($count);
            $bar->start();

            $inserted = $seeder->seed($count);

            $bar->finish();
            $this->newLine();
            $this->info("✓ Successfully inserted <comment>{$inserted}</comment> row(s) into the <comment>{$fqcn}</comment> table.");

        } catch (Throwable $e) {
            $this->newLine();
            $this->error('Seeding failed: ' . $e->getMessage());
            if ($this->getOutput()->isVerbose()) {
                $this->line($e->getTraceAsString());
            }
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Resolve the model FQCN from a short name or full class name.
     */
    private function resolveModel(string $model): ?string
    {
        foreach ($this->modelCandidates($model) as $candidate) {
            if (class_exists($candidate)) {
                return $candidate;
            }
        }
        return null;
    }

    /**
     * Return candidate FQCNs to check.
     */
    private function modelCandidates(string $model): array
    {
        return array_unique([
            $model,                                      // as-is (already FQCN)
            'App\\Models\\' . class_basename($model),   // Laravel convention
            'App\\' . class_basename($model),            // older Laravel
        ]);
    }
}
