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
    public function testShouldImplementsApiAwareAction()
    {
        $rc = new \ReflectionClass(MasspayAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }
    
    public function testThrowIfPayoutAlreadyAcknowledged()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('Payout has already been acknowledged');
        $action = new MasspayAction();

        $action->execute(new Masspay(['ACK' => 'foo'], 0));
    }

    public function testShouldCallApiMasspayMethodWithExpectedRequiredArguments()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('massPay')
            ->willReturnCallback(function (array $fields) {
                $this->assertSame(['foo' => 'fooVal'], $fields);

                return [];
            })
        ;

        $action = new MasspayAction();
        $action->setApi($apiMock);

        $request = new Masspay(['foo' => 'fooVal',]);

        $action->execute($request);
    }

    public function testShouldCallApiMasspayMethodAndUpdateModelFromResponseOnSuccess()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('massPay')
            ->willReturnCallback(function (array $fields) {
                $this->assertSame(['foo' => 'fooVal'], $fields);

                $fields['bar'] = 'barVal';

                return $fields;
            })
        ;

        $action = new MasspayAction();
        $action->setApi($apiMock);

        $request = new Masspay(['foo' => 'fooVal',]);

        $action->execute($request);

        $this->assertSame(['foo' => 'fooVal', 'bar' => 'barVal'], (array) $request->getModel());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class, [], [], '', false);
    }
}
