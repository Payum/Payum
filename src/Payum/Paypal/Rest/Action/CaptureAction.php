<?php

namespace Payum\Paypal\Rest\Action;

use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Rest\ApiContext;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Request\Capture;
use Payum\Core\Reply\HttpRedirect;

class CaptureAction extends GatewayAwareAction implements ApiAwareInterface
{
    /**
     * @param ApiContext
     */
    protected $api;

    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        if (false == $api instanceof ApiContext) {
            throw new UnsupportedApiException('Given api is not supported. Supported api is instance of ApiContext');
        }

        $this->api = $api;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request Capture */
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var Payment $model */
        $model = $request->getModel();

        if (
            false == isset($model->state) &&
            isset($model->payer->payment_method) &&
            'paypal' == $model->payer->payment_method
        ) {
            $model->create($this->api);

            foreach ($model->links as $link) {
                if ($link->rel == 'approval_url') {
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
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof Payment
        ;
    }
}
