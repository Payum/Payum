<?php

declare(strict_types=1);

namespace Payum\Skeleton\Tests;

use Payum\Core\Gateway\Capability;
use Payum\Core\GatewayInterface;
use Payum\Core\Handler\HandlerMap;
use Payum\Core\PayumBuilder;
use Payum\Skeleton\SkeletonConfig;
use Payum\Skeleton\SkeletonGateway;
use PHPUnit\Framework\TestCase;

final class SkeletonGatewayTest extends TestCase
{
    public function testConfigAndGatewayNameEachOther(): void
    {
        $this->assertSame(SkeletonConfig::class, (new SkeletonGateway())->configClass());
        $this->assertSame(SkeletonGateway::class, (new SkeletonConfig())->getGatewayClass());
    }

    public function testHandlersImplyTheirCapabilities(): void
    {
        $capabilities = HandlerMap::fromHandlers((new SkeletonGateway())->handlers())->capabilities();

        $this->assertEqualsCanonicalizing([
            Capability::Capture,
            Capability::Authorize,
            Capability::Refund,
            Capability::Cancel,
            Capability::Webhooks,
        ], $capabilities);
    }

    public function testRegisteredGatewayIsBuilt(): void
    {
        $payum = (new PayumBuilder())
            ->addDefaultStorages()
            ->registerGateway('skeleton', new SkeletonConfig(sandbox: true))
            ->getPayum()
        ;

        $this->assertInstanceOf(GatewayInterface::class, $payum->getGateway('skeleton'));
    }
}
