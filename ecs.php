<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([__DIR__ . '/src']);

    $ecsConfig->rule(YodaStyleFixer::class);

    $ecsConfig->sets([
        SetList::PSR_12,
        SetList::SPACES,
        SetList::DOCBLOCK,
        SetList::COMMENTS,
        SetList::PHPUNIT,
        SetList::NAMESPACES,
        SetList::CLEAN_CODE,
        SetList::ARRAY,
    ]);
};
