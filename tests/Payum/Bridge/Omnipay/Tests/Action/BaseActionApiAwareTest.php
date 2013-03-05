<?php
namespace Payum\Bridge\Omnipay\Tests\Action;

class BaseActionApiAwareTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionApiAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Bridge\Omnipay\Action\BaseActionApiAware');

        $this->assertTrue($rc->isSubclassOf('Payum\Action\ActionApiAwareInterface'));
    }

    /**
     * @test
     */
    public function shouldBeAbstract()
    {
        $rc = new \ReflectionClass('Payum\Bridge\Omnipay\Action\BaseActionApiAware');

        $this->assertTrue($rc->isAbstract());
    }

    /**
     * @test
     */
    public function shouldAllowSetApi()
    {
        $expectedApi = $this->createGatewayMock();

        $action = $this->getMockForAbstractClass('Payum\Bridge\Omnipay\Action\BaseActionApiAware');

        $action->setApi($expectedApi);

        $this->assertAttributeSame($expectedApi, 'gateway', $action);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Exception\UnsupportedApiException
     */
    public function throwIfUnsupportedApiGiven()
    {
        $action = $this->getMockForAbstractClass('Payum\Bridge\Omnipay\Action\BaseActionApiAware');

        $action->setApi(new \stdClass);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Omnipay\Common\GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMock('Omnipay\Common\GatewayInterface');
    }
}