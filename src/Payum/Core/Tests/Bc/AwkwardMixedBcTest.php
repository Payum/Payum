<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bc;

use Payum\Core\PayumBuilder;
use Payum\Core\Tests\Bc\Fixture\AwkwardMixedConfig;

/**
 * Porting one operation must not change what the others do, nor what gets persisted. This is the path where
 * StorageExtension and PersistStateMiddleware can both write the same payment.
 */
final class AwkwardMixedBcTest extends BcTestCase
{
    protected static function scenarios(): string
    {
        return 'awkward_mixed';
    }

    protected static function register(): callable
    {
        return static function (PayumBuilder $builder): void {
            $builder->registerGateway(Recorder::GATEWAY, new AwkwardMixedConfig());
        };
    }
}
