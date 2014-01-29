<?php
namespace Payum\Klarna\Checkout\Tests\Action\Api;

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
    public function shouldAllowSetApi()
    {
        $expectedApi = $this->createConnectorMock();
        
        $action = $this->getMockForAbstractClass('Payum\Klarna\Checkout\Action\Api\BaseApiAwareAction');
        
        $action->setApi($expectedApi);

        $this->assertAttributeSame($expectedApi, 'api', $action);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     */
    public function throwIfUnsupportedApiGiven()
    {
        $action = $this->getMockForAbstractClass('Payum\Klarna\Checkout\Action\Api\BaseApiAwareAction');

        $action->setApi(new \stdClass);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Klarna_Checkout_ConnectorInterface
     */
    protected function createConnectorMock()
    {
        return $this->getMock('Klarna_Checkout_ConnectorInterface');
    }
}