<?php

namespace Oluokunkabiru\AutoSeeder;

use ReflectionClass;

class ModelDiscovery
{
    /**
     * Scan a directory for Eloquent model classes.
     *
     * @param  string $directory  Absolute path to scan (e.g. app_path('Models'))
     * @param  string $namespace  Base namespace for the directory (e.g. 'App\Models')
     * @return array<int, array{name: string, fqcn: string, table: string}>
     */
    public static function discover(string $directory, string $namespace = 'App\\Models'): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $models = [];

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            // Build the FQCN from the relative path
            $basePath = realpath($directory);
            $filePath = realpath($file->getPathname());
            
            $relative = str_replace($basePath, '', $filePath);
            $relative = str_replace(['/', '\\'], '\\', $relative);
            $relative = ltrim($relative, '\\');
            
            $fqcn = rtrim($namespace, '\\') . '\\' . str_replace('.php', '', $relative);

            if (!class_exists($fqcn)) {
                continue;
            }

            try {
                $ref = new ReflectionClass($fqcn);
            } catch (\Throwable) {
                continue;
            }

            // Must be a concrete, instantiable Eloquent model
            if ($ref->isAbstract() || $ref->isInterface() || $ref->isTrait()) {
                continue;
            }

            if (!$ref->isSubclassOf(\Illuminate\Database\Eloquent\Model::class)) {
                continue;
            }

            // Get table name safely
            try {
                $instance = $ref->newInstanceWithoutConstructor();
                $table = method_exists($instance, 'getTable') ? $instance->getTable() : '';
            } catch (\Throwable) {
                $table = '';
            }

            $models[] = [
                'name'  => $ref->getShortName(),
                'fqcn'  => $fqcn,
                'table' => $table,
            ];
        }

        // Sort alphabetically by name
        usort($models, fn($a, $b) => strcmp($a['name'], $b['name']));

        return $models;
    }
}
