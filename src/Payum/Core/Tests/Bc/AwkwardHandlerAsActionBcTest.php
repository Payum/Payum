<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bc;

use Payum\Core\GatewayFactoryInterface;
use Payum\Core\PayumBuilder;
use Payum\Core\Tests\Bc\Fixture\AwkwardHalfPortedGatewayFactory;

/**
 * A handler running inside a gateway a 1.x factory still builds, through HandlerToActionAdapter.
 */
final class AwkwardHandlerAsActionBcTest extends BcTestCase
{
    protected static function scenarios(): string
    {
        return 'awkward_mixed';
    }

    protected static function register(): callable
    {
        return static function (PayumBuilder $builder): void {
            $builder
                ->addGatewayFactory('awkward_half_ported', static fn (array $config, GatewayFactoryInterface $core) => new AwkwardHalfPortedGatewayFactory($config, $core))
                ->addGateway(Recorder::GATEWAY, [
                    'factory' => 'awkward_half_ported',
                ])
            ;
        };
    }
}
