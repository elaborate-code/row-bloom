<?php

namespace ElaborateCode\RowBloom;

use ElaborateCode\RowBloom\Renderers\Sizing\PaperFormat;

class Options
{
    /**
     * .
     *
     * @param  int[]|string[]  $margins
     * - Like Css number number,number number,number,number,number.
     * - Unit in millimeter
     */
    public function __construct(
        public bool $displayHeaderFooter = false,
        // * special classes: date, url, title, pageNumber, totalPages
        public ?string $rawHeader = null,
        public ?string $rawFooter = null,

        public bool $printBackground = false,
        public bool $preferCSSPageSize = false,

        public ?int $perPage = null,

        public bool $landscape = false,
        public PaperFormat $format = PaperFormat::FORMAT_A4, // takes priority over width or height
        public ?string $width = null,
        public ?string $height = null,

        public array $margins = [57, 57, 57, 57], // TODO: singular

        public ?string $metadataTitle = null,
        public ?string $metadataAuthor = null,
        public ?string $metadataCreator = null,
        public ?string $metadataSubject = null,
        public ?string $metadataKeywords = null,

        // scale ?
        // font ?
        // security ?
        // compression ?
    ) {
    }

    // TODO: margins and size must have units; default to px
}
