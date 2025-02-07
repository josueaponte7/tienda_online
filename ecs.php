<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\FunctionNotation\FunctionDeclarationFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\Whitespace\NoExtraBlankLinesFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    // Define los directorios que deseas analizar
    $ecsConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    // Aplica reglas específicas usando el método `rule()`
    $ecsConfig->rule(NoExtraBlankLinesFixer::class);
    $ecsConfig->rule(FunctionDeclarationFixer::class);
    $ecsConfig->ruleWithConfiguration(OrderedImportsFixer::class, [
        'imports_order' => ['class', 'function', 'const'],
        'sort_algorithm' => 'alpha',
    ]);
    // Opcional: reglas a omitir
    $ecsConfig->skip([
        'no_php4_constructor',
    ]);
};
