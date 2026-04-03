<?php

namespace Oluokunkabiru\AutoSeeder;

use PDO;
use RuntimeException;

class ColumnInspector
{
    private PDO $pdo;
    private string $driver;

    public function __construct(PDO $pdo)
    {
        $this->pdo    = $pdo;
        $this->driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    /**
     * Return an array of column definitions for the given table.
     * Each entry: ['name' => string, 'type' => string, 'nullable' => bool, 'length' => int|null, 'values' => array (for enums)]
     */
    public function getColumns(string $table): array
    {
        switch ($this->driver) {
            case 'mysql':
            case 'mariadb':
                return $this->getMysqlColumns($table);
            case 'sqlite':
                return $this->getSqliteColumns($table);
            case 'pgsql':
                return $this->getPgsqlColumns($table);
            default:
                throw new RuntimeException("Unsupported PDO driver: {$this->driver}");
        }
    }

    // -------------------------------------------------------------------------
    // MySQL / MariaDB
    // -------------------------------------------------------------------------
    private function getMysqlColumns(string $table): array
    {
        $stmt = $this->pdo->query("DESCRIBE `{$table}`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $columns = [];

        foreach ($rows as $row) {
            $columns[] = $this->parseMysqlRow($row);
        }

        return $columns;
    }

    private function parseMysqlRow(array $row): array
    {
        $rawType = strtolower($row['Type']);  // e.g. "varchar(255)", "decimal(10,2)", "tinyint(1)"
        $type    = preg_replace('/\(.*\)/', '', $rawType); // strip -> "varchar", "decimal"

        // Extract length (single value in parentheses)
        $length    = null;
        $precision = null;
        $scale     = null;

        if (preg_match('/\((\d+),\s*(\d+)\)/', $rawType, $m)) {
            // Two-part: decimal(10,2) — precision + scale
            $precision = (int) $m[1];
            $scale     = (int) $m[2];
            $length    = $precision; // treat precision as overall length for convenience
        } elseif (preg_match('/\((\d+)\)/', $rawType, $m)) {
            // Single value: varchar(255), tinyint(1)
            $length = (int) $m[1];
        }

        // Extract enum/set values
        $values = [];
        if (in_array($type, ['enum', 'set']) && preg_match('/\((.+)\)/', $rawType, $m)) {
            $values = array_map(fn($v) => trim($v, "'"), explode(',', $m[1]));
        }

        return [
            'name'      => $row['Field'],
            'type'      => $type,
            'nullable'  => strtolower($row['Null']) === 'yes',
            'length'    => $length,
            'precision' => $precision,
            'scale'     => $scale,
            'values'    => $values,
            'unsigned'  => str_contains($rawType, 'unsigned'),
            'key'       => strtolower($row['Key'] ?? ''),
            'extra'     => strtolower($row['Extra'] ?? ''),
        ];
    }

    // -------------------------------------------------------------------------
    // SQLite
    // -------------------------------------------------------------------------
    private function getSqliteColumns(string $table): array
    {
        $stmt = $this->pdo->query("PRAGMA table_info(`{$table}`)");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $columns = [];

        foreach ($rows as $row) {
            $rawType = strtolower($row['type']); // e.g. "integer", "text", "varchar(100)", "decimal(10,2)"
            $type    = preg_replace('/\(.*\)/', '', $rawType);

            $length    = null;
            $precision = null;
            $scale     = null;

            if (preg_match('/\((\d+),\s*(\d+)\)/', $rawType, $m)) {
                $precision = (int) $m[1];
                $scale     = (int) $m[2];
                $length    = $precision;
            } elseif (preg_match('/\((\d+)\)/', $rawType, $m)) {
                $length = (int) $m[1];
            }

            $columns[] = [
                'name'      => $row['name'],
                'type'      => $type,
                'nullable'  => ((int) $row['notnull']) === 0,
                'length'    => $length,
                'precision' => $precision,
                'scale'     => $scale,
                'values'    => [],
                'unsigned'  => false,
                'key'       => $row['pk'] ? 'pri' : '',
                'extra'     => '',
            ];
        }

        return $columns;
    }

    // -------------------------------------------------------------------------
    // PostgreSQL
    // -------------------------------------------------------------------------
    private function getPgsqlColumns(string $table): array
    {
        $sql = "
            SELECT
                column_name,
                data_type,
                is_nullable,
                character_maximum_length,
                numeric_precision,
                numeric_scale
            FROM information_schema.columns
            WHERE table_name = :table
            ORDER BY ordinal_position
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':table' => $table]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $columns = [];

        foreach ($rows as $row) {
            $columns[] = [
                'name'      => $row['column_name'],
                'type'      => strtolower($row['data_type']),
                'nullable'  => strtolower($row['is_nullable']) === 'yes',
                'length'    => $row['character_maximum_length'],
                'precision' => $row['numeric_precision'] ?? null,
                'scale'     => $row['numeric_scale'] ?? null,
                'values'    => [],
                'unsigned'  => false,
                'key'       => '',
                'extra'     => '',
            ];
        }

        return $columns;
    }
}
