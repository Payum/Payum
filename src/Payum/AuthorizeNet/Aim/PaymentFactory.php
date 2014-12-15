<?php
namespace Payum\AuthorizeNet\Aim;

use Payum\AuthorizeNet\Aim\Action\FillOrderDetailsAction;
use Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet\AuthorizeNetAIM;
use Payum\Core\Action\CaptureOrderAction;
use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Action\GetHttpRequestAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Payment;
use Payum\Core\Extension\EndlessCycleDetectorExtension;

use Payum\AuthorizeNet\Aim\Action\CaptureAction;
use Payum\AuthorizeNet\Aim\Action\StatusAction;
use Payum\Core\PaymentFactoryInterface;

class PaymentFactory implements PaymentFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(array $options = array())
    {
        $options = ArrayObject::ensureArrayObject($options);
        $options->validateNotEmpty(array('loginId', 'transactionKey'));
        $options['sandbox'] = null === $options['sandbox'] ? true : (bool) $options['sandbox'];

        $api = new AuthorizeNetAIM($options['loginId'], $options['transactionKey']);
        $api->setSandbox($options['sandbox']);

        $payment = new Payment;

        $payment->addApi($api);
        
        $payment->addExtension(new EndlessCycleDetectorExtension);

        $payment->addAction(new CaptureAction);
        $payment->addAction(new FillOrderDetailsAction);
        $payment->addAction(new StatusAction);
        $payment->addAction(new GetHttpRequestAction);

        $payment->addAction(new CaptureOrderAction);

        $payment->addAction(new ExecuteSameRequestWithModelDetailsAction);

        return $payment;
    }
}