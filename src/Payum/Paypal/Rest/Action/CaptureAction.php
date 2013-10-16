<?php

namespace Payum\Paypal\Rest\Action;

use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Rest\ApiContext;
use Payum\Action\PaymentAwareAction;
use Payum\ApiAwareInterface;
use Payum\Exception\RequestNotSupportedException;
use Payum\Exception\UnsupportedApiException;
use Payum\Request\CaptureRequest;
use Payum\Request\RedirectUrlInteractiveRequest;

class CaptureAction extends PaymentAwareAction implements ApiAwareInterface
{
    /**
     * @param ApiContext
     */
    protected $api;

    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /**
         * @var $request CaptureRequest
         */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        /**
         * @var $payment Payment
         */
        if (
            false == isset($request->getModel()->state) &&
            isset($request->getModel()->getPayer()->payment_method) &&
            'paypal' == $request->getModel()->getPayer()->payment_method
        ) {
            $payment = $request->getModel();
            $payment->create($this->api);

            foreach($payment->getLinks() as $link) {
                if($link->getRel() == 'approval_url') {
                    throw new RedirectUrlInteractiveRequest($link->getHref());
                }
            }
        }

        if (
            false == isset($request->getModel()->state) &&
            isset($request->getModel()->getPayer()->payment_method) &&
            'credit_card' == $request->getModel()->getPayer()->payment_method
        ) {
            $payment = $request->getModel();
            $payment->create($this->api);
        }

        if (
            true == isset($request->getModel()->state) &&
            isset($request->getModel()->getPayer()->payment_method) &&
            'paypal' == $request->getModel()->getPayer()->payment_method
        ) {
            $payment = $request->getModel();

            $execution = new PaymentExecution();
            $execution->setPayer_id($_GET['PayerID']);

            //Execute the payment
            $payment->execute($execution, $this->api);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CaptureRequest &&
            $request->getModel() instanceof Payment
        ;
    }

    /**
     * @param mixed $api
     *
     * @throws UnsupportedApiException if the given Api is not supported.
     *
     * @return void
     */
    public function setApi($api)
    {
        if(false == $api instanceof ApiContext) {
            throw new UnsupportedApiException('Given api is not supported. Supported api is instance of ApiContext');
        }

        $this->api = $api;
    }
}