<?php

namespace Bywyd\LaravelQol\Utilities;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueryLogger
{
    protected static bool $enabled = false;
    protected static array $queries = [];

    public static function enable(): void
    {
        if (static::$enabled) {
            return;
        }

        static::$enabled = true;

        DB::listen(function ($query) {
            static::$queries[] = [
                'query' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $query->time,
                'connection' => $query->connectionName,
            ];
        });
    }

    public static function disable(): void
    {
        static::$enabled = false;
    }

    public static function getQueries(): array
    {
        return static::$queries;
    }

    public static function getTotalTime(): float
    {
        return array_sum(array_column(static::$queries, 'time'));
    }

    public static function getCount(): int
    {
        return count(static::$queries);
    }

    public static function clear(): void
    {
        static::$queries = [];
    }

    public static function logToFile(?string $channel = null): void
    {
        $logger = $channel ? Log::channel($channel) : Log::getFacadeRoot();

        foreach (static::$queries as $query) {
            $logger->debug('Query executed', $query);
        }
    }

    public static function getSlowestQueries(int $limit = 10): array
    {
        $queries = static::$queries;
        usort($queries, fn($a, $b) => $b['time'] <=> $a['time']);
        return array_slice($queries, 0, $limit);
    }

    public static function dump(): void
    {
        dump([
            'total_queries' => static::getCount(),
            'total_time' => static::getTotalTime() . ' ms',
            'queries' => static::$queries,
        ]);
    }
}
