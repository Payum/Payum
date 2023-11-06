<?php
namespace Payum\Paypal\ProHosted\Nvp\Tests\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Request\Sync;
use Payum\Paypal\ProHosted\Nvp\Action\SyncAction;
use Payum\Paypal\ProHosted\Nvp\Request\Api\GetTransactionDetails;

class SyncActionTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(SyncAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(SyncAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    public function testShouldSupportSyncAndArrayAsModelWhichHasPaymentRequestAmountSet()
    {
        $action = new SyncAction();

        $paymentDetails = array(
            'AMT' => 12,
        );

        $request = new Sync($paymentDetails);

        $this->assertTrue($action->supports($request));
    }

    public function testShouldSupportSyncAndArrayAsModelWhichHasPaymentRequestAmountSetToZero()
    {
        $action = new SyncAction();

        $paymentDetails = array(
            'AMT' => 0,
        );

        $request = new Sync($paymentDetails);

        $this->assertTrue($action->supports($request));
    }

    public function testShouldNotSupportAnythingNotSync()
    {
        $action = new SyncAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new SyncAction();

        $action->execute(new \stdClass());
    }

    public function testShouldRequestGetTransactionDetailsAndUpdateModelIfTransactionIdSetInModel()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ProHosted\Nvp\Request\Api\GetTransactionDetails'))
            ->willReturnCallback(function (GetTransactionDetails $request) {
                $model = $request->getModel();
                $model['foo'] = 'fooVal';
                $model['AMT'] = 33;
            })
        ;

        $action = new SyncAction();
        $action->setGateway($gatewayMock);

        $details = new \ArrayObject(array(
            'AMT' => 11,
            'txn_id' => 'aTxn_id',
        ));

        $action->execute($sync = new Sync($details));

        $this->assertArrayHasKey('foo', (array) $details);
        $this->assertSame('fooVal', $details['foo']);

        $this->assertArrayHasKey('AMT', (array) $details);
        $this->assertSame(33, $details['AMT']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Core\GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->createMock('Payum\Core\GatewayInterface');
    }
}
