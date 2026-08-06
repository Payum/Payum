<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector;
use Rector\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector;
use Rector\CodeQuality\Rector\FuncCall\UnwrapSprintfOneArgumentRector;
use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodeQuality\Rector\If_\ShortenElseIfRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\Config\RectorConfig;
use Rector\ValueObject\PhpVersion;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php80\Rector\FunctionLike\MixedTypeRector;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Php82\Rector\FuncCall\Utf8DecodeEncodeToMbConvertEncodingRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\NarrowIdenticalWithConsecutiveRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\SingleWithConsecutiveToWithRector;
use Rector\PHPUnit\PHPUnit100\Rector\StmtsAwareInterface\WithConsecutiveRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withImportNames()
    ->withPhpVersion(PhpVersion::PHP_82)
    ->withSets([
        LevelSetList::UP_TO_PHP_82,

        // PHP
        SetList::PHP_72,
        SetList::PHP_73,
        SetList::PHP_74,
        SetList::PHP_80,

        // Code Quality
        SetList::INSTANCEOF,

        // PHPUnit
        PHPUnitSetList::PHPUNIT_40,
        PHPUnitSetList::PHPUNIT_50,
        PHPUnitSetList::PHPUNIT_60,
        PHPUnitSetList::PHPUNIT_70,
        PHPUnitSetList::PHPUNIT_80,
        PHPUnitSetList::PHPUNIT_90,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
    ])
    ->withRules([
        SimplifyUselessVariableRector::class,
        ShortenElseIfRector::class,
        SimplifyIfReturnBoolRector::class,
        UnusedForeachValueToArrayKeysRector::class,
        ChangeArrayPushToArrayAssignRector::class,
        UnwrapSprintfOneArgumentRector::class,
        FlipTypeControlToUseExclusiveTypeRector::class,
        InlineConstructorDefaultToPropertyRector::class,
        AddVoidReturnTypeWhereNoReturnRector::class,
        Utf8DecodeEncodeToMbConvertEncodingRector::class,
    ])
    ->withSkip([
        ClassPropertyAssignToConstructorPromotionRector::class,
        MixedTypeRector::class,
        ReadOnlyPropertyRector::class,
        FirstClassCallableRector::class,
        ReturnNeverTypeRector::class,

        // The withConsecutive() rewrites all produce subtly wrong code against this
        // suite, so they stay off until it moves to PHPUnit 10 and the rewrites can
        // be done by hand:
        //  - narrowing willReturnOnConsecutiveCalls($stub) to willReturn($stub)
        //    changes behaviour, because ConsecutiveCalls invokes a nested stub
        //    while ReturnStub hands it back as-is -- returnCallback() and
        //    throwException() silently stop firing;
        //  - narrowing withConsecutive(['x'], ['x']) to with(['x']) treats the
        //    argument list as a single expected argument;
        //  - the PHPUnit 10 matcher idiom compares constraints with assertSame().
        WithConsecutiveRector::class,
        NarrowIdenticalWithConsecutiveRector::class,
        SingleWithConsecutiveToWithRector::class,

        AddVoidReturnTypeWhereNoReturnRector::class => [
            __DIR__ . '/src/Payum/Core/GatewayFactory.php',
        ],
    ]);
