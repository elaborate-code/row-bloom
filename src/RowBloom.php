<?php

namespace ElaborateCode\RowBloom;

use ElaborateCode\RowBloom\DataCollectors\DataCollectorFactory;
use ElaborateCode\RowBloom\Fs\File;
use ElaborateCode\RowBloom\Interpolators\Interpolator;
use ElaborateCode\RowBloom\Interpolators\InterpolatorFactory;
use ElaborateCode\RowBloom\Renderers\Renderer;
use ElaborateCode\RowBloom\Renderers\RendererFactory;
use ElaborateCode\RowBloom\Types\Css;
use ElaborateCode\RowBloom\Types\Html;
use ElaborateCode\RowBloom\Types\Table;

class RowBloom
{
    private Interpolator|InterpolatorContract|string $interpolator;

    private Renderer|RendererContract|string $renderer;

    /** @var (Table|array)[] */
    private array $tables = [];

    private Html|File|null $template = null;

    /** @var (Css|File)[] */
    private array $css = [];

    // ------------------------------------------------------------

    public function __construct(private Options $options)
    {
    }

    public function save(File|string $file): bool
    {
        $file = $file instanceof File ? $file : app()->make(File::class, ['path' => $file]);

        return $this->render()->save($file);
    }

    public function get(): string
    {
        return $this->render()->get();
    }

    private function render(): RendererContract
    {
        $interpolator = $this->resolveInterpolator();
        $renderer = $this->resolveRenderer();

        $finalTable = $this->table();
        $finaleTemplate = $this->template();
        $finalCss = $this->css();

        $interpolatedTemplate = $interpolator->interpolate($finaleTemplate, $finalTable, $this->options->perPage);

        return $renderer->render($interpolatedTemplate, $finalCss, $this->options);
    }

    // ------------------------------------------------------------

    private function table(): Table
    {
        if (empty($this->tables)) {
            throw new RowBloomException('A table is required');
        }

        $finalTable = Table::fromArray([]);

        foreach ($this->tables as $table) {
            if ($table instanceof Table) {
                $finalTable->append($table);
            } else {
                $finalTable->append($this->tableFromPath($table));
            }
        }

        return $finalTable;
    }

    private function tableFromPath(array $tablePath): Table
    {
        $dataCollectorFactory = app()->make(DataCollectorFactory::class);

        $table = null;

        if (isset($tablePath['driver'])) {
            $table = $dataCollectorFactory->make($tablePath['driver']);
        } else {
            $table = $dataCollectorFactory->makeFromPath($tablePath['path']);
        }

        return $table->getTable($tablePath['path']);
    }

    private function template(): Html
    {
        if (is_null($this->template)) {
            throw new RowBloomException('A template is required');
        }

        if ($this->template instanceof Html) {
            return $this->template;
        }

        if ($this->template instanceof File) {
            return Html::fromString($this->template->readFileContent());
        }
    }

    private function css(): Css
    {
        $finalCss = Css::fromString('');

        foreach ($this->css as $css) {
            if ($css instanceof Css) {
                $finalCss->append($css);
            } elseif ($css instanceof File) {
                $finalCss->append($css->readFileContent());
            }
        }

        return $finalCss;
    }

    // ============================================================
    // Fluent build methods
    // ============================================================

    public function addTable(Table|array $table): static
    {
        $this->tables[] = $table instanceof Table ? $table : Table::fromArray($table);

        return $this;
    }

    // ? addSpreadsheetPath() ,addJsonPath(), ...
    public function addTablePath(string $tablePath, ?string $driver = null): static
    {
        // ? improve type (TablePath...)
        $this->tables[] = [
            'path' => $tablePath,
            'driver' => $driver,
        ];

        return $this;
    }

    public function setTemplate(Html|string $template): static
    {
        $this->template = $template instanceof Html ? $template : Html::fromString($template);

        return $this;
    }

    public function setTemplatePath(File|string $templateFile): static
    {
        $templateFile = $templateFile instanceof File ? $templateFile : app()->make(File::class, ['path' => $templateFile]);

        $templateFile->mustExist()->mustBeReadable()->mustBeFile()->mustBeExtension('html');

        $this->template = $templateFile;

        return $this;
    }

    public function addCss(Css|string $css): static
    {
        $this->css[] = $css instanceof Css ? $css : Css::fromString($css);

        return $this;
    }

    public function addCssPath(File|string $cssFile): static
    {
        $cssFile = $cssFile instanceof File ? $cssFile : app()->make(File::class, ['path' => $cssFile]);

        $cssFile->mustExist()->mustBeReadable()->mustBeFile()->mustBeExtension('css');

        $this->css[] = $cssFile;

        return $this;
    }

    public function setOption(string $key, mixed $value): static
    {
        $this->options->$key = $value;

        return $this;
    }

    // ============================================================
    //
    // ============================================================

    public function setInterpolator(Interpolator|InterpolatorContract|string $interpolator): static
    {
        $this->interpolator = $interpolator;

        return $this;
    }

    public function setRenderer(Renderer|RendererContract|string $renderer): static
    {
        $this->renderer = $renderer;

        return $this;
    }

    private function resolveInterpolator(): InterpolatorContract
    {
        if (! isset($this->interpolator)) {
            throw new RowBloomException('Interpolator must be set');
        }

        if ($this->interpolator instanceof InterpolatorContract) {
            return $this->interpolator;
        }

        return app()->make(InterpolatorFactory::class)->make($this->interpolator);
    }

    private function resolveRenderer(): RendererContract
    {
        if (! isset($this->renderer)) {
            throw new RowBloomException('Renderer must be set');
        }

        if ($this->renderer instanceof RendererContract) {
            return $this->renderer;
        }

        return app()->make(RendererFactory::class)->make($this->renderer);
    }
}
