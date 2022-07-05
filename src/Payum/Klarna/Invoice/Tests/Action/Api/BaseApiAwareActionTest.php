<?php

namespace Payum\Klarna\Invoice\Tests\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class BaseApiAwareActionTest extends TestCase
{
    public function testShouldImplementsApiAwareInterface()
    {
        $rc = new ReflectionClass(BaseApiAwareAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testShouldImplementsActionInterface()
    {
        $rc = new ReflectionClass(BaseApiAwareAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldBeAbstracted()
    {
        $rc = new ReflectionClass(BaseApiAwareAction::class);

        $this->assertFalse($rc->isInstantiable());
    }

    public function testThrowApiNotSupportedIfNotConfigGivenAsApi()
    {
        $this->expectException(UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Klarna\Invoice\Config');
        $action = $this->getMockForAbstractClass(BaseApiAwareAction::class);

        $action->setApi(new stdClass());
    }
}
