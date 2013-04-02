<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action;

use Buzz\Message\Form\FormRequest;

use Payum\Paypal\ExpressCheckout\Nvp\Examples\Entity\RecurringPaymentDetails;
use Payum\Request\SyncRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response;
use Payum\Paypal\ExpressCheckout\Nvp\Action\RecurringPaymentDetailsSyncAction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

class RecurringPaymentDetailsSyncActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfPaymentAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Action\RecurringPaymentDetailsSyncAction');
        
        $this->assertTrue($rc->isSubclassOf('Payum\Action\PaymentAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()   
    {
        new RecurringPaymentDetailsSyncAction();
    }

    /**
     * @test
     */
    public function shouldSupportSyncRequestAndArrayAsModelWhichHasBillingPeriodSet()
    {
        $action = new RecurringPaymentDetailsSyncAction();

        $paymentDetails = array(
            'BILLINGPERIOD' => 12
        );
        
        $request = new SyncRequest($paymentDetails);

        $this->assertTrue($action->supports($request));
    }

    /**
     * @test
     */
    public function shouldSupportSyncRequestWithRecurringPaymentDetailsAsModel()
    {
        $action = new RecurringPaymentDetailsSyncAction();

        $paymentDetails = new RecurringPaymentDetails();
        $paymentDetails->setBillingperiod('foo');

        $this->assertTrue($action->supports(new SyncRequest($paymentDetails)));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotSyncRequest()
    {
        $action = new RecurringPaymentDetailsSyncAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     * 
     * @expectedException \Payum\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $action = new RecurringPaymentDetailsSyncAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldDoNothingIfProfileIdNotSet()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->never())
            ->method('execute')
        ;
        
        $action = new RecurringPaymentDetailsSyncAction();
        $action->setPayment($paymentMock);

        $request = new SyncRequest(array(
            'BILLINGPERIOD' => 12
        ));
        
        $action->execute($request);
    }

    /**
     * @test
     */
    public function shouldRequestGetRecurringPaymentsProfileDetailsActionIfProfileIdSetInModel()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetRecurringPaymentsProfileDetailsRequest'))
        ;

        $action = new RecurringPaymentDetailsSyncAction();
        $action->setPayment($paymentMock);

        $action->execute(new SyncRequest(array(
            'BILLINGPERIOD' => 'aBillingPeriod',
            'PROFILEID' => 'anId',
        )));
    }
    
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\PaymentInterface
     */
    protected function createPaymentMock()
    {
        return $this->getMock('Payum\PaymentInterface');
    }
}