<?php
namespace Payum\Klarna\Invoice\Tests\Action\Api;

use Payum\Core\Tests\SkipOnPhp7Trait;
use Payum\Klarna\Invoice\Config;
use PHPUnit\Framework\TestCase;

class BaseApiAwareActionTest extends TestCase
{
    use SkipOnPhp7Trait;

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
    public function shouldAllowSetConfigAsApi()
    {
        $action = $this->getMockForAbstractClass('Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction');

        $action->setApi($config = new Config());

        $this->assertAttributeSame($config, 'config', $action);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     * @expectedExceptionMessage Not supported api given. It must be an instance of Payum\Klarna\Invoice\Config
     */
    public function throwApiNotSupportedIfNotConfigGivenAsApi()
    {
        $action = $this->getMockForAbstractClass('Payum\Klarna\Invoice\Action\Api\BaseApiAwareAction');

        $action->setApi(new \stdClass());
    }
}
