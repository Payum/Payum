<?php

namespace Payum\Klarna\Checkout\Tests\Action\Api;

use Klarna_Checkout_ConnectorInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Klarna\Checkout\Action\Api\BaseApiAwareAction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class BaseApiAwareActionTest extends TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new ReflectionClass(BaseApiAwareAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    public function testShouldImplementApiAwareInterface()
    {
        $rc = new ReflectionClass(BaseApiAwareAction::class);

        $this->assertTrue($rc->isSubclassOf(ApiAwareInterface::class));
    }

    public function testShouldBeAbstract()
    {
        $rc = new ReflectionClass(BaseApiAwareAction::class);

        $this->assertTrue($rc->isAbstract());
    }

    public function testThrowIfUnsupportedApiGiven()
    {
        $this->expectException(UnsupportedApiException::class);
        $action = $this->getMockForAbstractClass(BaseApiAwareAction::class);

        $action->setApi(new stdClass());
    }

    protected function getActionClass(): string
    {
        return '';
    }

    protected function getApiClass()
    {
    }

    /**
     * @return MockObject|Klarna_Checkout_ConnectorInterface
     */
    protected function createConnectorMock()
    {
        return $this->createMock(Klarna_Checkout_ConnectorInterface::class);
    }
}
