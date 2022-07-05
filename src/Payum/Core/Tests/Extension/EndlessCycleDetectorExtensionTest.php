<?php

namespace Payum\Core\Tests\Extension;

use Payum\Core\Exception\LogicException;
use Payum\Core\Extension\Context;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Payum\Core\GatewayInterface;
use Payum\Core\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class EndlessCycleDetectorExtensionTest extends TestCase
{
    public function testShouldImplementExtensionInterface()
    {
        $rc = new \ReflectionClass(\Payum\Core\Extension\EndlessCycleDetectorExtension::class);

        $this->assertTrue($rc->implementsInterface(\Payum\Core\Extension\ExtensionInterface::class));
    }

    public function testThrowIfCycleCounterMoreOrEqualsToNumberOfPreviousRequest()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('Possible endless cycle detected. ::onPreExecute was called 2 times before reach the limit.');
        $gatewayMock = $this->createGatewayMock();

        $context = new Context($gatewayMock, new \stdClass(), [
            new Context($gatewayMock, new \stdClass(), []),
            new Context($gatewayMock, new \stdClass(), []),
            new Context($gatewayMock, new \stdClass(), []),
        ]);

        $extension = new EndlessCycleDetectorExtension($expectedLimit = 2);

        $extension->onPreExecute($context);
    }

    public function testShouldNotThrowIfNumberOfPreviousRequestNotReachLimit()
    {
        $this->expectNotToPerformAssertions();

        $gatewayMock = $this->createGatewayMock();

        $context = new Context($gatewayMock, new \stdClass(), [
            new Context($gatewayMock, new \stdClass(), []),
            new Context($gatewayMock, new \stdClass(), []),
            new Context($gatewayMock, new \stdClass(), []),
        ]);

        $extension = new EndlessCycleDetectorExtension($expectedLimit = 5);

        try {
            $extension->onPreExecute($context);
        } catch (LogicException $e) {
            $this->fail('Exception should not be thrown');
        }
    }

    /**
     * @return MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock(\Payum\Core\GatewayInterface::class);
    }
}
