<?php
namespace Payum\Paypal\ProHosted\Nvp\Tests\Action\Api;

class BaseApiAwareActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ProHosted\Nvp\Action\Api\BaseApiAwareAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function shouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ProHosted\Nvp\Action\Api\BaseApiAwareAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\ApiAwareInterface'));
    }

    /**
     * @test
     */
    public function shouldBeAbstract()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ProHosted\Nvp\Action\Api\BaseApiAwareAction');

        $this->assertTrue($rc->isAbstract());
    }

    /**
     * @test
     */
    public function shouldAllowSetApi()
    {
        $expectedApi = $this->createApiMock();

        $action = $this->getMockForAbstractClass('Payum\Paypal\ProHosted\Nvp\Action\Api\BaseApiAwareAction');

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
        $action = $this->getMockForAbstractClass('Payum\Paypal\ProHosted\Nvp\Action\Api\BaseApiAwareAction');

        $action->setApi(new \stdClass());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Paypal\ProHosted\Nvp\Api
     */
    protected function createApiMock()
    {
        return $this->getMock('Payum\Paypal\ProHosted\Nvp\Api', array(), array(), '', false);
    }
}
