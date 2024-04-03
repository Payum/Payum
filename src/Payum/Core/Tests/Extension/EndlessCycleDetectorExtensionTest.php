<?php

namespace Payum\Core\Tests\Extension;

use Payum\Core\Exception\LogicException;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Payum\Core\Extension\ExtensionInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use stdClass;

class EndlessCycleDetectorExtensionTest extends TestCase
{
    public function testShouldImplementExtensionInterface(): void
    {
        $rc = new ReflectionClass(EndlessCycleDetectorExtension::class);

        $this->assertTrue($rc->implementsInterface(ExtensionInterface::class));
    }

    public function testThrowIfCycleCounterMoreOrEqualsToNumberOfPreviousRequest(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Possible endless cycle detected. ::onPreExecute was called 2 times before reach the limit.');
        $gatewayMock = $this->createGatewayMock();

        $context = new Context($gatewayMock, new stdClass(), [
            new Context($gatewayMock, new stdClass(), []),
            new Context($gatewayMock, new stdClass(), []),
            new Context($gatewayMock, new stdClass(), []),
        ]);

        $extension = new EndlessCycleDetectorExtension($expectedLimit = 2);

        $extension->onPreExecute($context);
    }

    public function testShouldNotThrowIfNumberOfPreviousRequestNotReachLimit(): void
    {
        $this->expectNotToPerformAssertions();

        $gatewayMock = $this->createGatewayMock();

        $context = new Context($gatewayMock, new stdClass(), [
            new Context($gatewayMock, new stdClass(), []),
            new Context($gatewayMock, new stdClass(), []),
            new Context($gatewayMock, new stdClass(), []),
        ]);

        $extension = new EndlessCycleDetectorExtension($expectedLimit = 5);

        try {
            $extension->onPreExecute($context);
        } catch (LogicException) {
            $this->fail('Exception should not be thrown');
        }
    }

    /**
     * @return MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(GatewayInterface::class);
    }
}
