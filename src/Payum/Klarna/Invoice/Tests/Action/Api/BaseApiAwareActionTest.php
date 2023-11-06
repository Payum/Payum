<?php
namespace Payum\Klarna\Invoice\Tests\Action\Api;

use Payum\Klarna\Invoice\Config;
use PHPUnit\Framework\TestCase;

class BaseApiAwareActionTest extends TestCase
{
    public function testShouldImplementsApiAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\ApiAwareInterface'));
    }

    public function testShouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Action\ActionInterface'));
    }

    public function testShouldBeAbstracted()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction');

        $this->assertFalse($rc->isInstantiable());
    }

    public function testThrowApiNotSupportedIfNotConfigGivenAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Klarna\Invoice\Config');
        $action = $this->getMockForAbstractClass('Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction');

        $action->setApi(new \stdClass());
    }
}
