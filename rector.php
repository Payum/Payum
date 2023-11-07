<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php80\Rector\FunctionLike\MixedTypeRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\AddSeeTestAnnotationRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
    ]);

    $rectorConfig->importNames();
    $rectorConfig->importShortClasses();
    $rectorConfig->phpVersion(PhpVersion::PHP_80);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_80,

        // PHP
        SetList::PHP_72,
        SetList::PHP_73,
        SetList::PHP_74,
        SetList::PHP_80,

        // Code Quality
        // SetList::CODE_QUALITY,
        // SetList::INSTANCEOF,
        // SetList::STRICT_BOOLEANS,
        // SetList::TYPE_DECLARATION,

        // PHPUnit
        PHPUnitSetList::PHPUNIT_40,
        PHPUnitSetList::PHPUNIT_50,
        PHPUnitSetList::PHPUNIT_60,
        PHPUnitSetList::PHPUNIT_70,
        PHPUnitSetList::PHPUNIT_80,
        PHPUnitSetList::PHPUNIT_90,
        // PHPUnitSetList::PHPUNIT_100,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
    ]);

    $rectorConfig->skip([
        AddSeeTestAnnotationRector::class,
        // ClosureToArrowFunctionRector::class,
        ClassPropertyAssignToConstructorPromotionRector::class,

        TypedPropertyFromAssignsRector::class,
        JsonThrowOnErrorRector::class,
        MixedTypeRector::class,
    ]);
};
