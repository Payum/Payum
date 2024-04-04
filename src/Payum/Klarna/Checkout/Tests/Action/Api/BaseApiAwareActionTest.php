<?php
namespace Payum\Klarna\Checkout\Tests\Action\Api;

use PHPUnit\Framework\TestCase;

class BaseApiAwareActionTest extends TestCase
{
    protected function getActionClass(): string
    {
        // TODO: Implement getActionClass() method.
    }

    protected function getApiClass()
    {
        // TODO: Implement getApiClass() method.
    }

    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\Action\Api\BaseApiAwareAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\ActionInterface'));
    }

    public function testShouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\Action\Api\BaseApiAwareAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\ApiAwareInterface'));
    }

    public function testShouldBeAbstract()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\Action\Api\BaseApiAwareAction');

        $this->assertTrue($rc->isAbstract());
    }

    public function testThrowIfUnsupportedApiGiven()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $action = $this->getMockForAbstractClass('Payum\Klarna\Checkout\Action\Api\BaseApiAwareAction');

        $action->setApi(new \stdClass());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Klarna_Checkout_ConnectorInterface
     */
    protected function createConnectorMock()
    {
        return $this->createMock('Klarna_Checkout_ConnectorInterface');
    }
}
