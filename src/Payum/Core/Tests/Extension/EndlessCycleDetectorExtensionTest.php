<?php
namespace Payum\Core\Tests\Extension;

use Payum\Core\Extension\Context;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Payum\Core\GatewayInterface;

class EndlessCycleDetectorExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementExtensionInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Extension\EndlessCycleDetectorExtension');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Extension\ExtensionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new EndlessCycleDetectorExtension();
    }

    /**
     * @test
     */
    public function shouldSetDefaultLimitInConstructor()
    {
        $extension = new EndlessCycleDetectorExtension();

        $this->assertAttributeEquals(100, 'limit', $extension);
    }

    /**
     * @test
     */
    public function shouldAllowSetLimitInInConstructor()
    {
        $extension = new EndlessCycleDetectorExtension($expectedLimit = 55);

        $this->assertAttributeEquals($expectedLimit, 'limit', $extension);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage Possible endless cycle detected. ::onPreExecute was called 2 times before reach the limit.
     */
    public function throwIfCycleCounterMoreOrEqualsToNumberOfPreviousRequest()
    {
        $gatewayMock = $this->createGatewayMock();

        $context = new Context($gatewayMock, new \stdClass(), array(
            new Context($gatewayMock, new \stdClass(), array()),
            new Context($gatewayMock, new \stdClass(), array()),
            new Context($gatewayMock, new \stdClass(), array()),
        ));

        $extension = new EndlessCycleDetectorExtension($expectedLimit = 2);

        $extension->onPreExecute($context);
    }

    /**
     * @test
     */
    public function shouldNotThrowIfNumberOfPreviousRequestNotReachLimit()
    {
        $gatewayMock = $this->createGatewayMock();

        $context = new Context($gatewayMock, new \stdClass(), array(
            new Context($gatewayMock, new \stdClass(), array()),
            new Context($gatewayMock, new \stdClass(), array()),
            new Context($gatewayMock, new \stdClass(), array()),
        ));

        $extension = new EndlessCycleDetectorExtension($expectedLimit = 5);

        $extension->onPreExecute($context);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMock('Payum\Core\GatewayInterface');
    }
}
