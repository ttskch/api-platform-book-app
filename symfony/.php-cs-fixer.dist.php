<?php

$finder = (new PhpCsFixer\Finder())
    ->in(['src', 'tests'])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'phpdoc_summary' => false,
        'phpdoc_to_comment' => false,
        'nullable_type_declaration_for_default_null_value' => true,
        'ordered_interfaces' => true,
        'ordered_traits' => true,
        'yoda_style' => false,
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
