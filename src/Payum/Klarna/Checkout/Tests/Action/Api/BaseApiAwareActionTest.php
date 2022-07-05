<?php

namespace Payum\Klarna\Checkout\Tests\Action\Api;

use PHPUnit\Framework\TestCase;

class BaseApiAwareActionTest extends TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(\Payum\Klarna\Checkout\Action\Api\BaseApiAwareAction::class);

        $this->assertTrue($rc->isSubclassOf(\Payum\Core\Action\ActionInterface::class));
    }

    public function testShouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass(\Payum\Klarna\Checkout\Action\Api\BaseApiAwareAction::class);

        $this->assertTrue($rc->isSubclassOf(\Payum\Core\ApiAwareInterface::class));
    }

    public function testShouldBeAbstract()
    {
        $rc = new \ReflectionClass(\Payum\Klarna\Checkout\Action\Api\BaseApiAwareAction::class);

        $this->assertTrue($rc->isAbstract());
    }

    public function testThrowIfUnsupportedApiGiven()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $action = $this->getMockForAbstractClass(\Payum\Klarna\Checkout\Action\Api\BaseApiAwareAction::class);

        $action->setApi(new \stdClass());
    }

    protected function getActionClass(): string
    {
    }

    protected function getApiClass()
    {
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Klarna_Checkout_ConnectorInterface
     */
    protected function createConnectorMock()
    {
        return $this->createMock(\Klarna_Checkout_ConnectorInterface::class);
    }
}
