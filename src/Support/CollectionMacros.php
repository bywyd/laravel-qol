<?php

namespace Bywyd\LaravelQol\Support;

use Illuminate\Support\Collection;

class CollectionMacros
{
    public static function register(): void
    {
        Collection::macro('recursive', function () {
            return $this->map(function ($value) {
                if ($value instanceof Collection) {
                    return $value->recursive()->toArray();
                }
                if (is_array($value)) {
                    return collect($value)->recursive()->toArray();
                }
                return $value;
            });
        });

        Collection::macro('groupByMultiple', function (array $keys) {
            $result = $this;
            foreach (array_reverse($keys) as $key) {
                $result = $result->groupBy($key);
            }
            return $result;
        });

        Collection::macro('toCsv', function (array $headers = []) {
            $output = fopen('php://temp', 'r+');
            if (!empty($headers)) {
                fputcsv($output, $headers);
            }
            foreach ($this->items as $row) {
                fputcsv($output, is_array($row) ? $row : (array) $row);
            }
            rewind($output);
            $csv = stream_get_contents($output);
            fclose($output);
            return $csv;
        });

        Collection::macro('hasDuplicates', function (?string $key = null) {
            $items = $key ? $this->pluck($key) : $this;
            return $items->count() !== $items->unique()->count();
        });

        Collection::macro('transpose', function () {
            $items = array_map(function (...$items) {
                return $items;
            }, ...$this->values());
            return new static($items);
        });

        Collection::macro('stats', function (?string $key = null) {
            $values = $key ? $this->pluck($key) : $this;
            $count = $values->count();
            if ($count === 0) {
                return [
                    'count' => 0, 'sum' => 0, 'avg' => 0,
                    'min' => null, 'max' => null, 'median' => null,
                ];
            }
            $sorted = $values->sort()->values();
            $middle = (int) floor($count / 2);
            return [
                'count' => $count,
                'sum' => $values->sum(),
                'avg' => $values->avg(),
                'min' => $values->min(),
                'max' => $values->max(),
                'median' => $count % 2 === 0
                    ? ($sorted[$middle - 1] + $sorted[$middle]) / 2
                    : $sorted[$middle],
            ];
        });

        Collection::macro('filterNull', function () {
            return $this->filter(fn($item) => !is_null($item));
        });

        Collection::macro('filterEmpty', function () {
            return $this->filter(fn($item) => $item !== '');
        });

        Collection::macro('paginate', function (int $perPage = 15, ?int $page = null, string $pageName = 'page') {
            $page = $page ?: (\Illuminate\Pagination\Paginator::resolveCurrentPage($pageName));
            return new \Illuminate\Pagination\LengthAwarePaginator(
                $this->forPage($page, $perPage),
                $this->count(),
                $perPage,
                $page,
                [
                    'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                    'pageName' => $pageName,
                ]
            );
        });
    }
}
