<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bc\Fixture;

use Payum\Core\Config\GatewayConfig;

final class AwkwardAdaptedConfig implements GatewayConfig
{
    public function getGatewayClass(): string
    {
        return AwkwardAdaptedGateway::class;
    }
}
