<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Reference;

use Payum\Core\Gateway\GatewayInterface as PaymentGateway;
use Payum\Core\Legacy\UndescribedGateway;
use Payum\Core\Reference\ReferenceNamespace;
use PHPUnit\Framework\TestCase;

final class ReferenceNamespaceTest extends TestCase
{
    public function testShouldUseTheDescriptorClassRatherThanItsName(): void
    {
        $gateway = $this->createStub(PaymentGateway::class);
        $gateway->method('name')->willReturn('Acme');

        $this->assertSame($gateway::class, ReferenceNamespace::forGateway($gateway));
    }

    public function testShouldUseTheGatewayNameForAnUndescribedGateway(): void
    {
        $this->assertSame('legacy_paypal', ReferenceNamespace::forGateway(new UndescribedGateway('legacy_paypal')));
    }
}
