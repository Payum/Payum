<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Casing\MagicConstantCasingFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassDefinitionFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\ClassNotation\SelfAccessorFixer;
use PhpCsFixer\Fixer\ClassNotation\SingleClassElementPerStatementFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUselessElseFixer;
use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use PhpCsFixer\Fixer\FunctionNotation\VoidReturnFixer;
use PhpCsFixer\Fixer\LanguageConstruct\ExplicitIndirectVariableFixer;
use PhpCsFixer\Fixer\LanguageConstruct\FunctionToConstantFixer;
use PhpCsFixer\Fixer\Operator\NewWithBracesFixer;
use PhpCsFixer\Fixer\Operator\StandardizeIncrementFixer;
use PhpCsFixer\Fixer\Operator\UnaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitMethodCasingFixer;
use PhpCsFixer\Fixer\StringNotation\ExplicitStringVariableFixer;
use PhpCsFixer\Fixer\StringNotation\SingleQuoteFixer;
use PhpCsFixer\Fixer\Whitespace\TypesSpacesFixer;
use Symplify\CodingStandard\Fixer\Spacing\MethodChainingNewlineFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/rector.php',
        __FILE__,
    ])
    ->withPreparedSets(psr12: true, common: true, cleanCode: true)
    ->withRules(
        [
            YodaStyleFixer::class,
            PhpUnitMethodCasingFixer::class,
            FunctionToConstantFixer::class,
            ExplicitStringVariableFixer::class,
            ExplicitIndirectVariableFixer::class,
            NewWithBracesFixer::class,
            StandardizeIncrementFixer::class,
            SelfAccessorFixer::class,
            MagicConstantCasingFixer::class,
            NoUselessElseFixer::class,
            SingleQuoteFixer::class,
            OrderedClassElementsFixer::class,
            VoidReturnFixer::class,
        ]
    )
    ->withConfiguredRule(SingleClassElementPerStatementFixer::class, [
        'elements' => ['const', 'property'],
    ])
    ->withConfiguredRule(ClassDefinitionFixer::class, [
        'single_line' => \true,
    ])
    ->withConfiguredRule(GeneralPhpdocAnnotationRemoveFixer::class, [
        'annotations' => [],
    ])
    ->withSkip([
        MethodChainingNewlineFixer::class,
        TypesSpacesFixer::class,
        UnaryOperatorSpacesFixer::class,
    ]);
