<?php
namespace Payum\Paypal\ProHosted\Nvp\Tests\Action;

use Payum\Core\Request\Sync;
use Payum\Paypal\ProHosted\Nvp\Action\PaymentDetailsSyncAction;
use Payum\Paypal\ProHosted\Nvp\Api;
use Payum\Paypal\ProHosted\Nvp\Request\Api\GetTransactionDetails;

class PaymentDetailsSyncActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGatewayAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ProHosted\Nvp\Action\PaymentDetailsSyncAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\GatewayAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new PaymentDetailsSyncAction();
    }

    /**
     * @test
     */
    public function shouldSupportSyncAndArrayAsModelWhichHasPaymentRequestAmountSet()
    {
        $action = new PaymentDetailsSyncAction();

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
        $action = new PaymentDetailsSyncAction();

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
        $action = new PaymentDetailsSyncAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new PaymentDetailsSyncAction();

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

        $action = new PaymentDetailsSyncAction();
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
