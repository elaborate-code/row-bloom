<?php

namespace ElaborateCode\RowBloom\Renderers;

use ElaborateCode\RowBloom\RendererContract;
use Exception;

final class RendererFactory
{
    public function make(Renderer|string $driver): RendererContract
    {
        if ($driver instanceof Renderer) {
            return app()->make($driver->value);
        }

        if (is_a($driver, RendererContract::class, true)) {
            return app()->make($driver);
        }

        throw new Exception("'{$driver}' is not a valid renderer");
    }
}
