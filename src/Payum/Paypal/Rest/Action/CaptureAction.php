<?php

namespace Payum\Paypal\Rest\Action;

use PayPal\Api\Payment as PaypalPayment;
use PayPal\Api\PaymentExecution;
use PayPal\Rest\ApiContext;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Reply\HttpRedirect;

class CaptureAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;

    public function __construct()
    {
        $this->apiClass = ApiContext::class;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request Capture */
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaypalPayment $model */
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
            $request->getModel() instanceof PaypalPayment
        ;
    }
}
