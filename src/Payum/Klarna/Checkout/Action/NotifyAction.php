<?php
namespace Payum\Klarna\Checkout\Action;

use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Sync;
use Payum\Klarna\Checkout\Constants;
use Payum\Klarna\Checkout\Request\Api\UpdateOrder;

class NotifyAction extends GatewayAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param Notify $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute(new Sync($details));

        if (Constants::STATUS_CHECKOUT_COMPLETE == $details['status']) {
            $this->gateway->execute(new UpdateOrder(array(
                'location' => $details['location'],
                'status' => Constants::STATUS_CREATED,
                'merchant_reference' => array(
                    'orderid1' => $details['merchant_reference']['orderid1'],
                ),
            )));

            $this->gateway->execute(new Sync($details));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Notify &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
