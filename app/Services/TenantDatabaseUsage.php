<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class TenantDatabaseUsage
{
    /**
     * @return array{
     *     driver: string,
     *     database: string,
     *     host: string|null,
     *     port: string|int|null,
     *     usage_bytes: int|null,
     *     table_count: int|null
     * }
     */
    public static function summarizeTenantConnection(): array
    {
        $config = config('database.connections.tenant', []);
        $driver = (string) ($config['driver'] ?? 'mysql');
        $database = (string) ($config['database'] ?? '');

        return [
            'driver' => $driver,
            'database' => $database,
            'host' => $config['host'] ?? null,
            'port' => $config['port'] ?? null,
            'usage_bytes' => self::approximateSizeBytes($driver, $database, $config),
            'table_count' => self::tableCount($driver, $database),
        ];
    }

    private static function approximateSizeBytes(string $driver, string $database, array $config): ?int
    {
        try {
            if (in_array($driver, ['mysql', 'mariadb'], true) && $database !== '') {
                $row = DB::connection('tenant')->selectOne(
                    'SELECT COALESCE(SUM(data_length + index_length), 0) AS size FROM information_schema.tables WHERE table_schema = ?',
                    [$database]
                );

                return (int) ($row->size ?? 0);
            }

            if ($driver === 'pgsql') {
                $row = DB::connection('tenant')->selectOne('SELECT pg_database_size(current_database()) AS size');

                return (int) ($row->size ?? 0);
            }

            if ($driver === 'sqlite') {
                $path = $config['database'] ?? '';
                if ($path === '' || $path === ':memory:') {
                    return null;
                }
                $resolved = self::resolveSqlitePath((string) $path);
                if ($resolved !== null && is_file($resolved)) {
                    $sz = filesize($resolved);

                    return $sz !== false ? (int) $sz : null;
                }

                return null;
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    private static function resolveSqlitePath(string $path): ?string
    {
        if ($path === '' || str_starts_with($path, ':')) {
            return null;
        }
        if (is_file($path)) {
            return $path;
        }
        $base = base_path($path);
        if (is_file($base)) {
            return $base;
        }

        return null;
    }

    private static function tableCount(string $driver, string $database): ?int
    {
        try {
            if (in_array($driver, ['mysql', 'mariadb'], true) && $database !== '') {
                $row = DB::connection('tenant')->selectOne(
                    "SELECT COUNT(*) AS c FROM information_schema.tables WHERE table_schema = ? AND table_type = 'BASE TABLE'",
                    [$database]
                );

                return (int) ($row->c ?? 0);
            }

            if ($driver === 'pgsql') {
                $row = DB::connection('tenant')->selectOne(
                    "SELECT COUNT(*) AS c FROM information_schema.tables WHERE table_schema NOT IN ('pg_catalog', 'information_schema') AND table_type = 'BASE TABLE'"
                );

                return (int) ($row->c ?? 0);
            }

            if ($driver === 'sqlite') {
                $row = DB::connection('tenant')->selectOne(
                    "SELECT COUNT(*) AS c FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'"
                );

                return (int) ($row->c ?? 0);
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }
}
