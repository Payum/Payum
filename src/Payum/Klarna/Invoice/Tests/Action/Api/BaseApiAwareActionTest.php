<?php

namespace Payum\Klarna\Invoice\Tests\Action\Api;

use PHPUnit\Framework\TestCase;

class BaseApiAwareActionTest extends TestCase
{
    public function testShouldImplementsApiAwareInterface()
    {
        $rc = new \ReflectionClass(\Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction::class);

        $this->assertTrue($rc->implementsInterface(\Payum\Core\ApiAwareInterface::class));
    }

    public function testShouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass(\Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction::class);

        $this->assertTrue($rc->implementsInterface(\Payum\Core\Action\ActionInterface::class));
    }

    public function testShouldBeAbstracted()
    {
        $rc = new \ReflectionClass(\Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction::class);

        $this->assertFalse($rc->isInstantiable());
    }

    public function testThrowApiNotSupportedIfNotConfigGivenAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Klarna\Invoice\Config');
        $action = $this->getMockForAbstractClass(\Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction::class);

        $action->setApi(new \stdClass());
    }
}
