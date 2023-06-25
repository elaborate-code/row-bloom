<?php

namespace ElaborateCode\RowBloom\Renderers;

use ElaborateCode\RowBloom\Options;
use Exception;

final class Margin
{
    public static $defaultUnit = 'px';

    private string $unit;

    /** @var float[]|int[] */
    private array $value = [];

    public static function fromOptions(Options $options, ?string $unit = null)
    {
        return new self($options->margins, $unit);
    }

    public function __construct(array $margin, ?string $unit = null)
    {
        $this->unit = $unit ?? self::$defaultUnit;

        $count = count($margin);

        if ($count === 1) {
            $this->set('marginTop', $margin[0]);
            $this->set('marginRight', $margin[0]);
            $this->set('marginBottom', $margin[0]);
            $this->set('marginLeft', $margin[0]);
        } elseif ($count === 2) {
            $this->set('marginTop', $margin[0]);
            $this->set('marginRight', $margin[0]);
            $this->set('marginBottom', $margin[1]);
            $this->set('marginLeft', $margin[1]);
        } elseif ($count === 4) {
            $this->set('marginTop', $margin[0]);
            $this->set('marginRight', $margin[1]);
            $this->set('marginBottom', $margin[2]);
            $this->set('marginLeft', $margin[3]);
        } else {
            throw new Exception('Invalid margin');
        }
    }

    private function set($key, $value): void
    {
        $value = trim($value);

        if (preg_match('/^\d+(\.\d+)?$/', $value)) {
            $value = $value;
        } elseif (preg_match('/^\d+(\.\d+)?\s+[[:alpha:]]+$/', $value)) {
            // TODO: convert
            $value = $value;
        } else {
            throw new Exception('Invalid margin');
        }

        $this->value[$key] = (float) $value;
    }

    public function get(string $key): string
    {
        return $this->value[$key] ?? '';
    }

    public function all(): array
    {
        return $this->value;
    }

    // ! relative values cannot be converted

    // TODO: add unit support getIn(), allIn() ... Or add convert()
}
