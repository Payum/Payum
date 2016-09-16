<?php
namespace Payum\Paypal\ProHosted\Nvp\Tests\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Request\Sync;
use Payum\Paypal\ProHosted\Nvp\Action\SyncAction;
use Payum\Paypal\ProHosted\Nvp\Request\Api\GetTransactionDetails;

class SyncActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementActionInterface()
    {
        $rc = new \ReflectionClass(SyncAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementGatewayAwareInterface()
    {
        $rc = new \ReflectionClass(SyncAction::class);

        $this->assertTrue($rc->implementsInterface(GatewayAwareInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new SyncAction();
    }

    /**
     * @test
     */
    public function shouldSupportSyncAndArrayAsModelWhichHasPaymentRequestAmountSet()
    {
        $action = new SyncAction();

        $paymentDetails = array(
            'AMT' => 12,
        );

        $request = new Sync($paymentDetails);

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldSupportSyncAndArrayAsModelWhichHasPaymentRequestAmountSetToZero()
    {
        $action = new SyncAction();

        $paymentDetails = array(
            'AMT' => 0,
        );

        $request = new Sync($paymentDetails);

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotSync()
    {
        $action = new SyncAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new SyncAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldRequestGetTransactionDetailsAndUpdateModelIfTransactionIdSetInModel()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ProHosted\Nvp\Request\Api\GetTransactionDetails'))
            ->will($this->returnCallback(function (GetTransactionDetails $request) {
                $model = $request->getModel();
                $model['foo'] = 'fooVal';
                $model['AMT'] = 33;
            }))
        ;

        $action = new SyncAction();
        $action->setGateway($gatewayMock);

        $details = new \ArrayObject(array(
            'AMT' => 11,
            'txn_id' => 'aTxn_id',
        ));

        $action->execute($sync = new Sync($details));

        $this->assertArrayHasKey('foo', (array) $details);
        $this->assertEquals('fooVal', $details['foo']);

        $this->assertArrayHasKey('AMT', (array) $details);
        $this->assertEquals(33, $details['AMT']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Core\GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMock('Payum\Core\GatewayInterface');
    }
}
