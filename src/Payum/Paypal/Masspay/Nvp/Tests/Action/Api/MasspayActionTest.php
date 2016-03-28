<?php
namespace Payum\Paypal\Masspay\Nvp\Tests\Action\Api;

use Payum\Core\ApiAwareInterface;
use Payum\Core\Tests\GenericActionTest;
use Payum\Paypal\Masspay\Nvp\Action\Api\MasspayAction;
use Payum\Paypal\Masspay\Nvp\Api;
use Payum\Paypal\Masspay\Nvp\Request\Api\Masspay;

class MasspayActionTest extends GenericActionTest
{
    protected $requestClass = Masspay::class;

    protected $actionClass = MasspayAction::class;
    /**
     * @test
     */
    public function shouldImplementsApiAwareAction()
    {
        $rc = new \ReflectionClass(MasspayAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }
    
    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage Payout has already been acknowledged
     */
    public function throwIfPayoutAlreadyAcknowledged()
    {
        $action = new MasspayAction();

        $action->execute(new Masspay(['ACK' => 'foo'], 0));
    }

    /**
     * @test
     */
    public function shouldCallApiMasspayMethodWithExpectedRequiredArguments()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('massPay')
            ->will($this->returnCallback(function (array $fields) {
                $this->assertEquals(['foo' => 'fooVal'], $fields);

                return [];
            }))
        ;

        $action = new MasspayAction();
        $action->setApi($apiMock);

        $request = new Masspay(['foo' => 'fooVal',]);

        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldCallApiMasspayMethodAndUpdateModelFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('massPay')
            ->will($this->returnCallback(function (array $fields) {
                $this->assertEquals(['foo' => 'fooVal'], $fields);

                $fields['bar'] = 'barVal';

                return $fields;
            }))
        ;

        $action = new MasspayAction();
        $action->setApi($apiMock);

        $request = new Masspay(['foo' => 'fooVal',]);

        $action->execute($request);

        $this->assertEquals(['foo' => 'fooVal', 'bar' => 'barVal'], (array) $request->getModel());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->getMock(Api::class, [], [], '', false);
    }
}
