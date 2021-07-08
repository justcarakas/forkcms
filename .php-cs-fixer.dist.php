<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/src')
    ->exclude(
        [
            'Themes',
            'bootstrap.php',
        ]
    );

return (new PhpCsFixer\Config())
    ->setRules(
        [
            '@Symfony' => true,
            'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
            'phpdoc_align' => ['align' => 'left'],
            'concat_space' => ['spacing' => 'one'],
        ]
    )
    ->setFinder($finder);
