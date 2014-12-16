<?php
namespace Payum\Payex;

use Payum\Core\Action\CaptureOrderAction;
use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Action\GetHttpRequestAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\PaymentFactoryInterface;
use Payum\Payex\Action\AgreementDetailsStatusAction;
use Payum\Payex\Action\Api\AutoPayAgreementAction;
use Payum\Payex\Action\Api\CheckAgreementAction;
use Payum\Payex\Action\Api\CheckOrderAction;
use Payum\Payex\Action\Api\CheckRecurringPaymentAction;
use Payum\Payex\Action\Api\CreateAgreementAction;
use Payum\Payex\Action\Api\DeleteAgreementAction;
use Payum\Payex\Action\Api\StartRecurringPaymentAction;
use Payum\Payex\Action\Api\StopRecurringPaymentAction;
use Payum\Payex\Action\FillOrderDetailsAction;
use Payum\Payex\Action\PaymentDetailsSyncAction;
use Payum\Core\Payment;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Payum\Payex\Action\Api\CompleteOrderAction;
use Payum\Payex\Action\Api\InitializeOrderAction;
use Payum\Payex\Action\PaymentDetailsCaptureAction;
use Payum\Payex\Action\PaymentDetailsStatusAction;
use Payum\Payex\Action\AutoPayPaymentDetailsCaptureAction;
use Payum\Payex\Action\AutoPayPaymentDetailsStatusAction;
use Payum\Payex\Api\AgreementApi;
use Payum\Payex\Api\OrderApi;
use Payum\Payex\Api\RecurringApi;
use Payum\Payex\Api\SoapClientFactory;

class PaymentFactory implements PaymentFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(array $options = array())
    {
        $options = ArrayObject::ensureArrayObject($options);
        $options->defaults(array(
            'accountNumber' => '',
            'encryptionKey' => '',
            'sandbox' => true,
        ));

        $soapClientFactory = new SoapClientFactory();

        $payment = new Payment;

        $payment->addApi(new OrderApi($soapClientFactory, (array) $options));
        $payment->addApi(new AgreementApi($soapClientFactory, (array) $options));
        $payment->addApi(new RecurringApi($soapClientFactory, (array) $options));

        // agreement actions
        $payment->addAction(new AgreementDetailsStatusAction);
        $payment->addAction(new CreateAgreementAction);
        $payment->addAction(new DeleteAgreementAction);
        $payment->addAction(new CheckAgreementAction);
        $payment->addAction(new AutoPayAgreementAction);

        //recurring actions
        $payment->addAction(new StartRecurringPaymentAction);
        $payment->addAction(new StopRecurringPaymentAction);
        $payment->addAction(new CheckRecurringPaymentAction);

        $payment->addExtension(new EndlessCycleDetectorExtension);

        $payment->addAction(new InitializeOrderAction);
        $payment->addAction(new CompleteOrderAction);
        $payment->addAction(new CheckOrderAction);
        
        $payment->addAction(new PaymentDetailsCaptureAction);
        $payment->addAction(new FillOrderDetailsAction);
        $payment->addAction(new PaymentDetailsStatusAction);
        $payment->addAction(new PaymentDetailsSyncAction);
        $payment->addAction(new AutoPayPaymentDetailsCaptureAction);
        $payment->addAction(new AutoPayPaymentDetailsStatusAction);
        $payment->addAction(new GetHttpRequestAction);

        $payment->addAction(new CaptureOrderAction);

        $payment->addAction(new ExecuteSameRequestWithModelDetailsAction);

        return $payment;
    }

    /**
     */
    private  function __construct()
    {
    }
}