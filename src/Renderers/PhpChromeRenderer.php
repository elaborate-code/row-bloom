<?php

namespace ElaborateCode\RowBloom\Renderers;

use ElaborateCode\RowBloom\Fs\File;
use ElaborateCode\RowBloom\Options;
use ElaborateCode\RowBloom\RendererContract;
use ElaborateCode\RowBloom\Renderers\Sizing\LengthUnit;
use ElaborateCode\RowBloom\Renderers\Sizing\Margin;
use ElaborateCode\RowBloom\Types\Css;
use ElaborateCode\RowBloom\Types\Html;
use HeadlessChromium\BrowserFactory;

/**
 * requires ext-sockets
 *
 * https://github.com/chrome-php/chrome#print-as-pdf
 *
 * Chromium browser based converter
 *
 * Pros:
 * - Strong HTML and CSS support
 * - Js support
 *
 * Cons
 * - requires chromium
 * - Other PDF specific attributes...
 */
class PhpChromeRenderer implements RendererContract
{
    protected string $rendering;

    protected Html $html;

    protected Css $css;

    protected Options $options;

    protected array $phpChromeOptions = [
        // 'landscape'           => true,             // default to false
        // 'printBackground'     => true,             // default to false
        // 'displayHeaderFooter' => true,             // default to false
        // 'preferCSSPageSize'   => true,             // default to false (reads parameters directly from @page)
        // 'marginTop'           => 0.0,              // defaults to ~0.4 (must be a float, value in inches)
        // 'marginRight'         => 1.0,              // defaults to ~0.4 (must be a float, value in inches)
        // 'marginBottom'        => 1.4,              // defaults to ~0.4 (must be a float, value in inches)
        // 'marginLeft'          => 5.0,              // defaults to ~0.4 (must be a float, value in inches)
        // 'paperWidth'          => 6.0,              // defaults to 8.5 (must be a float, value in inches)
        // 'paperHeight'         => 6.0,              // defaults to 11.0 (must be a float, value in inches)
        // 'headerTemplate'      => '<div>foo</div>', // see details above
        // 'footerTemplate'      => '<div>foo</div>', // see details above
        // 'scale'               => 1.2,              // defaults to 1.0 (must be a float)
    ];

    public function get(): string
    {
        return $this->rendering;
    }

    public function save(File $file): bool
    {
        return $file->mustBeExtension('pdf')
            ->startSaving()
            ->streamFilterAppend('convert.base64-decode')
            ->save($this->rendering);
    }

    public function render(Html $html, Css $css, Options $options): static
    {
        $this->html = $html;
        $this->css = $css;
        $this->options = $options;

        $browserFactory = app()->make(BrowserFactory::class);
        // Start a browser and create a page
        $browser = $browserFactory->createBrowser();
        $page = $browser->createPage();

        $page->navigate('data:text/html,'.$this->html())
            ->waitForNavigation();

        $this->setPageFormat();
        $this->setMargin();
        $this->setHeaderAndFooter();
        $this->phpChromeOptions['displayHeaderFooter'] = $this->options->displayHeaderFooter;
        $this->phpChromeOptions['printBackground'] = $this->options->printBackground;
        $this->phpChromeOptions['preferCSSPageSize'] = $this->options->preferCSSPageSize;

        $this->rendering = $page->pdf($this->phpChromeOptions)->getBase64();
        // ! PHP Fatal error:  Uncaught HeadlessChromium\Exception\PdfFailed: Cannot make a PDF. Reason : -32000 - Printing failed in vendor\chrome-php\chrome\src\PageUtils\PagePdf.php:119
        //      happens when page sizings isn't correct (margin left overlaps on margin right ...)

        $browser->close();

        return $this;
    }

    // ============================================================
    // Html
    // ============================================================

    private function html(): string
    {
        // ? same happens in HtmlRenderer
        return <<<_HTML
            <!DOCTYPE html>
            <head>
                <style>
                    $this->css
                </style>
                <title>Row bloom</title>
            </head>
            <body>
                $this->html
            </body>
            </html>
        _HTML;
    }

    // ============================================================
    // Options
    // ============================================================

    private function setPageFormat(): void
    {
        [$this->phpChromeOptions['paperWidth'], $this->phpChromeOptions['paperHeight']]
            = $this->options->resolvePaperSize(LengthUnit::INCH_UNIT);
    }

    private function setHeaderAndFooter(): void
    {
        // ! style="font-size:10px" needs to be specified on header and footer

        $this->phpChromeOptions['displayHeaderFooter'] = $this->options->displayHeaderFooter;

        if ($this->options->displayHeaderFooter) {
            $this->phpChromeOptions['headerTemplate'] = $this->options->rawHeader ?? '';
            $this->phpChromeOptions['footerTemplate'] = $this->options->rawFooter ?? '';
        }
    }

    private function setMargin(): void
    {
        $this->phpChromeOptions =
            Margin::fromOptions($this->options)->allRawIn(LengthUnit::INCH_UNIT) +
            $this->phpChromeOptions;
    }
}
