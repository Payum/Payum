<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Php54\Rector\Array_\LongArrayToShortArrayRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php71\Rector\ClassConst\PublicConstantVisibilityRector;
use Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
    ]);

    //$rectorConfig->importNames();
    //$rectorConfig->importShortClasses();
    $rectorConfig->phpVersion(PhpVersion::PHP_72);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_72
    ]);

    $rectorConfig->skip([
        LongArrayToShortArrayRector::class,
        StringClassNameToClassConstantRector::class,
        RemoveExtraParametersRector::class,
        PublicConstantVisibilityRector::class,
    ]);
};
