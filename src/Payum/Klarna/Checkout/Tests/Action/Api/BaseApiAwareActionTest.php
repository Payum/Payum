<?php
namespace Payum\Klarna\Checkout\Tests\Action\Api;

use Payum\Klarna\Checkout\Config;

class BaseApiAwareActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\Action\Api\BaseApiAwareAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\Action\Api\BaseApiAwareAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\ApiAwareInterface'));
    }

    /**
     * @test
     */
    public function shouldBeAbstract()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\Action\Api\BaseApiAwareAction');

        $this->assertTrue($rc->isAbstract());
    }

    /**
     * @test
     */
    public function shouldAllowSetConfigAsApi()
    {
        $action = $this->getMockForAbstractClass('Payum\Klarna\Checkout\Action\Api\BaseApiAwareAction');

        $config = new Config();

        $action->setApi($config);

        $this->assertAttributeSame($config, 'config', $action);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     */
    public function throwIfUnsupportedApiGiven()
    {
        $action = $this->getMockForAbstractClass('Payum\Klarna\Checkout\Action\Api\BaseApiAwareAction');

        $action->setApi(new \stdClass());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Klarna_Checkout_ConnectorInterface
     */
    protected function createConnectorMock()
    {
        return $this->getMock('Klarna_Checkout_ConnectorInterface');
    }
}
