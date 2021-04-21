<?php
namespace Payum\Klarna\CheckoutRest\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Sync;
use Payum\Klarna\CheckoutRest\Request\Api\UpdateOrder;
use Payum\Klarna\Common\Constants;

class NotifyAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

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
            $this->gateway->execute((new UpdateOrder(
                [
                    'order_id' => $details['order_id'],
                ]))
                ->setMerchantReferences($details['merchant_reference']['orderid1'], null)
                ->acknowledgeOrder());

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
