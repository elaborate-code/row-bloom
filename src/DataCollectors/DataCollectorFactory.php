<?php

namespace ElaborateCode\RowBloom\DataCollectors;

use ElaborateCode\RowBloom\DataCollectorContract;
use ElaborateCode\RowBloom\Fs\File;
use ElaborateCode\RowBloom\Utils\BasicSingletonConcern;
use Exception;

final class DataCollectorFactory
{
    use BasicSingletonConcern;

    public function make(DataCollector|string $driver): DataCollectorContract
    {
        if ($driver instanceof DataCollector) {
            return new $driver->value;
        }

        if (class_exists($driver) && in_array(DataCollectorContract::class, class_implements($driver), true)) {
            /** @phpstan-ignore-next-line */
            return new $driver;
        }

        throw new Exception("'{$driver}' is not a valid data collector");
    }

    public function makeFromPath(string $path): DataCollectorContract
    {
        $file = File::fromPath($path);

        $driver = match (true) {
            $file->exists() => $this->resolveFileDriver($file),
            default => throw new Exception("Couldn't resolve a driver for the path '{$path}'"),
        };

        return $this->make($driver);
    }

    private function resolveFileDriver(File $file): DataCollector
    {
        if (in_array($file->extension(), ['xlsx', 'xls', 'xml', 'ods', 'slk', 'gnumeric', 'html', 'csv'], true)) {
            return DataCollector::Spreadsheet;
        }

        throw new Exception("Couldn't resolve a driver for the file '{$file}'");
    }
}
