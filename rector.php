<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector;
use Rector\CodeQuality\Rector\FuncCall\UnwrapSprintfOneArgumentRector;
use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Rector\CodeQuality\Rector\If_\ShortenElseIfRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\PHPUnit\CodeQuality\Rector\Expression\AssertArrayCastedObjectToAssertSameRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertEmptyNullableObjectToAssertInstanceofRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\NarrowIdenticalWithConsecutiveRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\SingleWithConsecutiveToWithRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withImportNames()
    ->withPhpVersion(PhpVersion::PHP_81)
    ->withSets([
        LevelSetList::UP_TO_PHP_85,

        // Code Quality
        SetList::INSTANCEOF,

        // PHPUnit
        PHPUnitSetList::COMPOSER_BASED,
        PHPUnitSetList::PHPUNIT_MOCK_TO_STUB,
        PHPUnitSetList::PHPUNIT_NARROW_ASSERTS,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
    ])
    ->withRules([
        SimplifyUselessVariableRector::class,
        ShortenElseIfRector::class,
        SimplifyIfReturnBoolRector::class,
        ChangeArrayPushToArrayAssignRector::class,
        UnwrapSprintfOneArgumentRector::class,
        InlineConstructorDefaultToPropertyRector::class,
        AddVoidReturnTypeWhereNoReturnRector::class,
    ])
    ->withSkip([
        ClassPropertyAssignToConstructorPromotionRector::class,
        ReadOnlyPropertyRector::class,
        ReturnNeverTypeRector::class,
        AssertArrayCastedObjectToAssertSameRector::class,

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
        NarrowIdenticalWithConsecutiveRector::class,
        SingleWithConsecutiveToWithRector::class,

        // assertNotInstanceOf(PaymentStatus::class, $x) passes for null AND for any other object,
        // whereas assertNull($x) pins the one value these tests mean:
        AssertEmptyNullableObjectToAssertInstanceofRector::class,

        AddVoidReturnTypeWhereNoReturnRector::class => [
            __DIR__ . '/src/Payum/Core/GatewayFactory.php',
        ],
    ]);
