<?php

namespace Payum\Paypal\Rest\Action;

use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Rest\ApiContext;
use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Request\Capture;
use Payum\Core\Reply\HttpRedirect;

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
         * @var $request \Payum\Core\Request\Capture
         */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        /** @var Payment $model */
        $model = $request->getModel();

        if (
            false == isset($model->state) &&
            isset($model->payer->payment_method) &&
            'paypal' == $model->payer->payment_method
        ) {
            $model->create($this->api);

            foreach($model->links as $link) {
                if($link->rel == 'approval_url') {
                    throw new HttpRedirect($link->href);
                }
            }
        }

        if (
            false == isset($model->state) &&
            isset($model->payer->payment_method) &&
            'credit_card' == $model->payer->payment_method
        ) {
            $model->create($this->api);
        }

        if (
            true == isset($model->state) &&
            isset($model->payer->payment_method) &&
            'paypal' == $model->payer->payment_method
        ) {
            $execution = new PaymentExecution();
            $execution->payer_id = $_GET['PayerID'];

            //Execute the payment
            $model->execute($execution, $this->api);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof Payment
        ;
    }

    /**
     * @param mixed $api
     *
     * @throws \Payum\Core\Exception\UnsupportedApiException if the given Api is not supported.
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