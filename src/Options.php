<?php

namespace ElaborateCode\RowBloom;

use ElaborateCode\RowBloom\Renderers\Sizing\LengthUnit;
use ElaborateCode\RowBloom\Renderers\Sizing\PaperFormat;

class Options
{
    /**
     * .
     *
     * **Default unit** for `$margin`, `$width`, and `$height` is millimeter (mm)
     *
     * @param  (float|int|string)[]|string  $margin
     * - Format like CSS (all_sides)|(top_bottom right_left)|(top right_left bottom)|(number number number number).
     * - Only string types support adding unit, numerical types fallback to *default unit*.
     * @param  ?PaperFormat  $format
     * - Takes precedence over `$width` and `$height`
     * - Affected by `$landscape`
     * - If no size indicator is given, A4 paper format will be used.
     */
    public function __construct(
        public bool $displayHeaderFooter = true,
        public ?string $rawHeader = null,
        public ?string $rawFooter = null,
        // TODO: handle special classes: date, url, title, pageNumber, totalPages [, header, footer]

        public bool $printBackground = false,
        public bool $preferCSSPageSize = false,

        public ?int $perPage = null,

        public bool $landscape = false,
        public ?PaperFormat $format = null,
        public ?string $width = null,
        public ?string $height = null,

        // TODO: unify behavior across drivers
        public array|string $margin = '1 in',

        public ?string $metadataTitle = null,
        public ?string $metadataAuthor = null,
        public ?string $metadataCreator = null,
        public ?string $metadataSubject = null,
        public ?string $metadataKeywords = null,

        // TODO: font

        // scale ?
        // security ?
        // compression ?
    ) {
    }

    public function resolvePaperSize(LengthUnit $unit): array
    {
        if (isset($this->format)) {
            $size = $this->format->size($unit);

            return $this->landscape ? [$size[1], $size[0]] : $size;
        }

        if (isset($this->width) && isset($this->height)) {
            return [$this->width, $this->height];
        }

        $size = PaperFormat::FORMAT_A4->size($unit);

        return $this->landscape ? [$size[1], $size[0]] : $size;
    }
}
