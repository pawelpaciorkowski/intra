<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->notPath('config')
    ->notPath('public')
    ->notPath('templates')
    ->notPath('translations')
    ->notPath('var')
    ->notPath('vendor')
    ->notPath('node_modules');

$config = new PhpCsFixer\Config();

return $config->setRules([
    '@PSR12' => true,
    'strict_param' => true,
    'no_unused_imports' => true,
    'array_syntax' => ['syntax' => 'short'],
])
    ->setFinder($finder);