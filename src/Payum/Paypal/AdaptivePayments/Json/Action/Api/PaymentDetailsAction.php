<?php
namespace Payum\Paypal\AdaptivePayments\Json\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\AdaptivePayments\Json\Api;
use Payum\Paypal\AdaptivePayments\Json\Request\Api\PaymentDetails;

/**
 * @property Api $api
 */
class PaymentDetailsAction extends BaseAction
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $model['payKey'] && false == $model['transactionId'] && false == $model['trackingId']) {
            throw new LogicException('One of payKey, transactionId, or trackingId field is required to identify the payment.');
        }

        $this->setDefaultErrorLanguage($model);
        $this->setDefaultDetailLevel($model);

        $model->replace($this->api->getPaymentDetails($model));
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof PaymentDetails &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}