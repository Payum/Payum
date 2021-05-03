<?php
namespace Payum\Klarna\Invoice\Tests\Action\Api;

use Payum\Klarna\Invoice\Config;
use PHPUnit\Framework\TestCase;

class BaseApiAwareActionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplementsApiAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\ApiAwareInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function shouldBeAbstracted()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction');

        $this->assertFalse($rc->isInstantiable());
    }

    /**
     * @test
     */
    public function throwApiNotSupportedIfNotConfigGivenAsApi()
    {
        $this->expectException(\Payum\Core\Exception\UnsupportedApiException::class);
        $this->expectExceptionMessage('Not supported api given. It must be an instance of Payum\Klarna\Invoice\Config');
        $action = $this->getMockForAbstractClass('Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction');

        $action->setApi(new \stdClass());
    }
}
