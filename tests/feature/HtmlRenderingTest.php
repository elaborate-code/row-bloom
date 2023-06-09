<?php

use ElaborateCode\RowBloom\Interpolators\Interpolator;
use ElaborateCode\RowBloom\Renderers\HtmlRenderer;
use ElaborateCode\RowBloom\Renderers\Renderer;
use ElaborateCode\RowBloom\RowBloom;
use ElaborateCode\RowBloom\Types\Css;
use ElaborateCode\RowBloom\Types\Html;
use ElaborateCode\RowBloom\Types\Table;

it('Basic html output', function (RowBloom $r, $css, $template, $table) {
    $r->setRenderer(Renderer::Html)->setInterpolator(Interpolator::Twig)
        ->addCss($css)
        ->setTemplate($template)
        ->addTable($table);

    expect($r->get())
        ->toBeString()
        ->toContain('ilies', 'mohamed');
})
    ->with([
        'Default' => app()->make(RowBloom::class),
        'Twig' => (app()->make(RowBloom::class))
            ->setInterpolator(Interpolator::Twig),
        'Html' => (app()->make(RowBloom::class))
            ->setRenderer(Renderer::Html),
        'HtmlRenderer instance' => (app()->make(RowBloom::class))
            ->setRenderer(app()->make(HtmlRenderer::class)),
    ])
    ->with([
        'primitives' => [
            'css' => '',
            'template' => '<h1>hey {{ name }}</h1>',
            'table' => [['name' => 'mohamed'], ['name' => 'ilies']],
        ],
        'types' => [
            'css' => Css::fromString(''),
            'template' => Html::fromString('<h1>hey {{ name }}</h1>'),
            'table' => Table::fromArray([['name' => 'mohamed'], ['name' => 'ilies']]),
        ],
    ]);
