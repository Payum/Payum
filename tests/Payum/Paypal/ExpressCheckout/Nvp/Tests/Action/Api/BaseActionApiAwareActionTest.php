<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

class BaseActionApiAwareActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\BaseActionApiAware');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\BaseActionApiAware');
        
        $this->assertTrue($rc->isSubclassOf('Payum\ApiAwareInterface'));
    }
    
    /**
     * @test
     */
    public function shouldBeAbstract()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\BaseActionApiAware');

        $this->assertTrue($rc->isAbstract());
    }

    /**
     * @test
     */
    public function shouldAllowSetApi()
    {
        $expectedApi = $this->createApiMock();
        
        $action = $this->getMockForAbstractClass('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\BaseActionApiAware');
        
        $action->setApi($expectedApi);

        $this->assertAttributeSame($expectedApi, 'api', $action);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\UnsupportedApiException
     */
    public function throwIfUnsupportedApiGiven()
    {
        $action = $this->getMockForAbstractClass('Payum\Paypal\ExpressCheckout\Nvp\Action\Api\BaseActionApiAware');

        $action->setApi(new \stdClass);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->getMock('Payum\Paypal\ExpressCheckout\Nvp\Api', array(), array(), '', false);
    }
}