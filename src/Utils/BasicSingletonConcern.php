<?php

namespace ElaborateCode\RowBloom\Utils;

trait BasicSingletonConcern
{
    /** @phpstan-ignore-next-line */
    private static $instance;

    private function __construct()
    {
    }

    public static function getInstance(): static
    {
        if (! self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }
}
