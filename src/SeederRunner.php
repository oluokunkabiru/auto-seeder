<?php

namespace Oluokunkabiru\AutoSeeder;

use PDO;
use RuntimeException;

class SeederRunner
{
    private PDO $pdo;
    private ColumnInspector $inspector;
    private RandomDataGenerator $generator;

    /** Columns to always skip during seeding */
    private array $skipColumns = [
        'id', 'deleted_at',
    ];

    public function __construct(PDO $pdo, string $locale = 'en_US')
    {
        $this->pdo       = $pdo;
        $this->inspector = new ColumnInspector($pdo);
        $this->generator = new RandomDataGenerator($locale);
    }

    /**
     * Seed the given table with $count random rows.
     *
     * @param  string $table   The database table name.
     * @param  int    $count   Number of rows to insert (default: 1).
     * @return int             Number of rows successfully inserted.
     */
    public function run(string $table, int $count = 1): int
    {
        if ($count < 1) {
            throw new RuntimeException("Count must be at least 1, got: {$count}");
        }

        $columns = $this->inspector->getColumns($table);
        $columns = $this->filterColumns($columns);

        if (empty($columns)) {
            throw new RuntimeException("No seedable columns found for table: {$table}");
        }

        $inserted = 0;
        $fkCache = [];

        for ($i = 0; $i < $count; $i++) {
            $row = [];
            foreach ($columns as $column) {
                // Determine if this is a foreign key tracking _id
                $colName = $column['name'];
                if (str_ends_with(strtolower($colName), '_id')) {
                    $relatedTable = $this->guessRelatedTable($colName);
                    
                    if (!isset($fkCache[$relatedTable])) {
                        try {
                            $stmt = $this->pdo->query("SELECT id FROM `{$relatedTable}`");
                            $fkCache[$relatedTable] = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
                        } catch (\Throwable $e) {
                            $fkCache[$relatedTable] = [];
                        }
                    }
                    
                    if (!empty($fkCache[$relatedTable])) {
                        $row[$colName] = $fkCache[$relatedTable][array_rand($fkCache[$relatedTable])];
                        continue;
                    }
                    // Fallthrough to standard generation if table is missing or empty
                }

                $row[$colName] = $this->generator->generate($column);
            }
            $this->insertRow($table, $row);
            $inserted++;
        }

        return $inserted;
    }

    /**
     * Guess the related table name for a foreign key column.
     * e.g. user_id -> users, company_id -> companies
     */
    private function guessRelatedTable(string $columnName): string
    {
        $base = str_replace('_id', '', strtolower($columnName));
        if (class_exists(\Illuminate\Support\Str::class)) {
            return \Illuminate\Support\Str::plural($base);
        }
        
        if (str_ends_with($base, 'y')) {
            return substr($base, 0, -1) . 'ies';
        }
        return $base . 's';
    }

    /**
     * Remove auto-increment PKs, timestamps, and other skip-listed columns.
     */
    private function filterColumns(array $columns): array
    {
        return array_values(array_filter($columns, function (array $col): bool {
            // Skip columns in the skip list
            if (in_array(strtolower($col['name']), $this->skipColumns)) {
                return false;
            }
            // Skip auto-increment primary keys
            if ($col['key'] === 'pri' && str_contains($col['extra'], 'auto_increment')) {
                return false;
            }
            return true;
        }));
    }

    /**
     * Build and execute a single INSERT statement for the given row.
     */
    private function insertRow(string $table, array $row): void
    {
        $driver  = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $quote   = $driver === 'pgsql' ? '"' : '`';

        $cols    = implode(', ', array_map(fn($c) => "{$quote}{$c}{$quote}", array_keys($row)));
        $placeholders = implode(', ', array_fill(0, count($row), '?'));

        $sql  = "INSERT INTO {$quote}{$table}{$quote} ({$cols}) VALUES ({$placeholders})";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($row));
    }

    /**
     * Add extra column names to always skip.
     *
     * @param  string[] $columns
     * @return $this
     */
    public function skipColumns(array $columns): static
    {
        $this->skipColumns = array_merge(
            $this->skipColumns,
            array_map('strtolower', $columns)
        );
        return $this;
    }

    /**
     * Set per-column format options (forwarded to RandomDataGenerator).
     *
     * @param  array<string, array<string, mixed>> $options
     * @return $this
     */
    public function setColumnOptions(array $options): static
    {
        $this->generator->setColumnOptions($options);
        return $this;
    }
}
