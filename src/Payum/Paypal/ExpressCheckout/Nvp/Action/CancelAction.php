<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Cancel;
use Payum\Core\Request\Sync;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoVoid;

class CancelAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function execute($request)
    {
        /** @var Cancel $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (! $details['TRANSACTIONID']) {
            return;
        }

        $voidDetails = new ArrayObject([
            'AUTHORIZATIONID' => $details['TRANSACTIONID'],
        ]);

        $this->gateway->execute(new DoVoid($voidDetails));
        $this->gateway->execute(new Sync($request->getModel()));
    }

    public function supports($request)
    {
        if (false == ($request instanceof Cancel && $request->getModel() instanceof ArrayAccess)) {
            return false;
        }

        // it must take into account only common payments, recurring payments must be cancelled by another action.
        $model = $request->getModel();
        return empty($model['BILLINGPERIOD']);
    }
}
