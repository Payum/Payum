<?php
namespace Payum\Payex;

use Payum\Action\SyncDetailsAggregatedModelAction;
use Payum\Payex\Action\Api\CheckOrderAction;
use Payum\Payex\Action\PaymentDetailsSyncAction;
use Payum\Payment;
use Payum\Extension\EndlessCycleDetectorExtension;
use Payum\Payex\Action\Api\AutoPayAgreementAction;
use Payum\Payex\Action\Api\CheckAgreementAction;
use Payum\Payex\Action\Api\CompleteOrderAction;
use Payum\Payex\Action\Api\CreateAgreementAction;
use Payum\Payex\Action\Api\DeleteAgreementAction;
use Payum\Payex\Action\Api\InitializeOrderAction;
use Payum\Payex\Action\Api\CheckRecurringPaymentAction;
use Payum\Payex\Action\Api\StartRecurringPaymentAction;
use Payum\Payex\Action\Api\StopRecurringPaymentAction;
use Payum\Payex\Action\PaymentDetailsCaptureAction;
use Payum\Payex\Action\PaymentDetailsStatusAction;
use Payum\Payex\Action\AgreementDetailsStatusAction;
use Payum\Payex\Action\AutoPayPaymentDetailsCaptureAction;
use Payum\Payex\Action\AutoPayPaymentDetailsStatusAction;
use Payum\Payex\Api\RecurringApi;
use Payum\Payex\Api\AgreementApi;
use Payum\Payex\Api\OrderApi;

abstract class PaymentFactory
{
    /**
     * @param Api\OrderApi $orderApi
     * @param Api\AgreementApi $agreementApi
     * @param Api\RecurringApi $recurringApi
     * 
     * @return \Payum\Payment
     */
    public static function create(OrderApi $orderApi, AgreementApi $agreementApi = null, RecurringApi $recurringApi = null)
    {
        $payment = new Payment;
        
        if ($agreementApi) {
            $payment->addApi($agreementApi);
            
            $payment->addAction(new AgreementDetailsStatusAction);
            $payment->addAction(new SyncDetailsAggregatedModelAction);
            
            $payment->addAction(new CreateAgreementAction);
            $payment->addAction(new DeleteAgreementAction);
            $payment->addAction(new CheckAgreementAction);
            $payment->addAction(new AutoPayAgreementAction);
        }

        if ($recurringApi) {
            $payment->addApi($recurringApi);
            
            $payment->addAction(new StartRecurringPaymentAction);
            $payment->addAction(new StopRecurringPaymentAction);
            $payment->addAction(new CheckRecurringPaymentAction);
        }

        $payment->addApi($orderApi);

        $payment->addExtension(new EndlessCycleDetectorExtension);

        $payment->addAction(new InitializeOrderAction);
        $payment->addAction(new CompleteOrderAction);
        $payment->addAction(new CheckOrderAction);
        
        $payment->addAction(new PaymentDetailsCaptureAction);
        $payment->addAction(new PaymentDetailsStatusAction);
        $payment->addAction(new PaymentDetailsSyncAction);
        $payment->addAction(new AutoPayPaymentDetailsCaptureAction);
        $payment->addAction(new AutoPayPaymentDetailsStatusAction);

        return $payment;
    }

    /**
     */
    private  function __construct()
    {
    }
}