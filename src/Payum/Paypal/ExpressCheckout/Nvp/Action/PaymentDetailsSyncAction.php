<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Sync;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetExpressCheckoutDetails;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetTransactionDetails;

class PaymentDetailsSyncAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function execute($request)
    {
        /** @var Sync $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $model['TOKEN']) {
            return;
        }

        $copiedModel = new ArrayObject([
            'TOKEN' => $model['TOKEN'],
        ]);

        $this->gateway->execute(new GetExpressCheckoutDetails($copiedModel));
        if (Api::L_ERRORCODE_SESSION_HAS_EXPIRED != $copiedModel['L_ERRORCODE0']) {
            $model->replace($copiedModel);
        }

        foreach (range(0, 9) as $index) {
            if ($model['PAYMENTREQUEST_' . $index . '_TRANSACTIONID']) {
                $this->gateway->execute(new GetTransactionDetails($model, $index));
            }
        }
    }

    public function supports($request)
    {
        if (false == $request instanceof Sync) {
            return false;
        }

        $model = $request->getModel();
        if (false == $model instanceof ArrayAccess) {
            return false;
        }

        return false == isset($model['BILLINGPERIOD']);
    }
}
