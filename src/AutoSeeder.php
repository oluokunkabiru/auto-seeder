<?php

namespace Oluokunkabiru\AutoSeeder;

use PDO;
use RuntimeException;

/**
 * AutoSeeder — public entry point.
 *
 * Usage (standalone):
 *   AutoSeeder::fromPdo($pdo)->seed('users', 10);
 *
 * Usage (Eloquent / Laravel):
 *   AutoSeeder::fromModel(User::class)->seed(50);
 *   AutoSeeder::fromModel(new User())->seed();       // 1 row (default)
 *   AutoSeeder::fromModel('App\Models\User', 50);    // shorthand
 *
 * Format options:
 *   AutoSeeder::fromModel(User::class)
 *       ->configure([
 *           'email' => ['domain' => 'acme.com'],      // email@acme.com
 *           'phone' => ['country_code' => '+234'],    // +234XXXXXXXXXX
 *       ])
 *       ->seed(50);
 */
class AutoSeeder
{
    private SeederRunner $runner;
    private string $table = '';

    private function __construct(SeederRunner $runner, string $table = '')
    {
        $this->runner = $runner;
        $this->table  = $table;
    }

    // =========================================================================
    // Factory methods
    // =========================================================================

    /**
     * Create from a raw PDO connection.
     * No table is pre-set; you must pass the table name to seed().
     */
    public static function fromPdo(PDO $pdo, string $locale = 'en_US'): static
    {
        return new static(new SeederRunner($pdo, $locale));
    }

    /**
     * Create from an Eloquent model class name or object.
     * Automatically resolves the PDO connection and table name.
     *
     * @param  string|object $model  FQCN or model instance
     * @param  int           $count  Optional: if provided, seeds immediately and returns row count.
     */
    public static function fromModel($model, int $count = 0): static|int
    {
        [$pdo, $table] = self::resolveModel($model);
        $instance = new static(new SeederRunner($pdo), $table);

        // Shorthand: AutoSeeder::fromModel(User::class, 50)
        if ($count > 0) {
            return $instance->seed($count);
        }

        return $instance;
    }

    // =========================================================================
    // Fluent API
    // =========================================================================

    /**
     * Seed rows into the resolved or specified table.
     *
     * @param  int    $count  Number of rows to insert (default: 1).
     * @param  string $table  Override table name (optional when model is used).
     * @return int            Number of rows inserted.
     */
    public function seed(int $count = 1, string $table = ''): int
    {
        $targetTable = $table ?: $this->table;

        if (empty($targetTable)) {
            throw new RuntimeException(
                'No table specified. Use fromModel() or pass a table name to seed().'
            );
        }

        return $this->runner->run($targetTable, $count);
    }

    /**
     * Add column names that should never be seeded.
     */
    public function skip(array $columns): static
    {
        $this->runner->skipColumns($columns);
        return $this;
    }

    /**
     * Set per-column format options.
     *
     * Supported options per column:
     *   'email' column  → ['domain' => 'example.com']
     *   'phone' column  → ['country_code' => '+234']
     *
     * The key is matched against column names by exact or partial match.
     * You may configure multiple columns at once:
     *
     *   ->configure([
     *       'email'         => ['domain' => 'acme.com'],
     *       'phone'         => ['country_code' => '+234'],
     *       'mobile_number' => ['country_code' => '+44'],
     *   ])
     *
     * @param  array<string, array<string, mixed>> $options
     * @return $this
     */
    public function configure(array $options): static
    {
        $this->runner->setColumnOptions($options);
        return $this;
    }

    // =========================================================================
    // Model resolution helpers
    // =========================================================================

    /**
     * Resolve a PDO connection and table name from an Eloquent model.
     *
     * @param  string|object $model
     * @return array{0: PDO, 1: string}
     */
    private static function resolveModel($model): array
    {
        // Instantiate if a class name string is given
        if (is_string($model)) {
            if (!class_exists($model)) {
                throw new RuntimeException("Model class not found: {$model}");
            }
            $model = new $model();
        }

        // Must be an object from here on
        if (!is_object($model)) {
            throw new RuntimeException('Model must be a class name string or an object instance.');
        }

        // --- Eloquent (Laravel) ---
        if (method_exists($model, 'getConnection') && method_exists($model, 'getTable')) {
            /** @var \Illuminate\Database\Eloquent\Model $model */
            $connection = $model->getConnection();
            $pdo        = $connection->getPdo();
            $table      = $model->getTable();
            return [$pdo, $table];
        }

        // --- Generic: model exposes getPdo() and getTable() ---
        if (method_exists($model, 'getPdo') && method_exists($model, 'getTable')) {
            return [$model->getPdo(), $model->getTable()];
        }

        throw new RuntimeException(
            get_class($model) . ' does not implement getConnection()/getTable() or getPdo()/getTable(). ' .
            'Use AutoSeeder::fromPdo($pdo)->seed($table, $count) instead.'
        );
    }
}
