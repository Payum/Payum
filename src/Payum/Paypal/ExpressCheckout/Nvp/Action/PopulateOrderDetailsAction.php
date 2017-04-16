<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\OrderInterface;
use Payum\Core\Request\PopulateOrderDetails;

class PopulateOrderDetailsAction implements ActionInterface
{

    /**
     * {@inheritDoc}
     *
     * @param PopulateOrderDetails $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var OrderInterface $order */
        $order = $request->getModel();

        $details = ArrayObject::ensureArrayObject($order->getDetails() ?: array());
        $details->replace(array(
            'PAYMENTREQUEST_0_CURRENCYCODE' => $order->getTotalCurrency(),
            'PAYMENTREQUEST_0_AMT' => $order->getTotalAmount() / 100,
        ));

        $order->setDetails((array) $details);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof PopulateOrderDetails &&
            $request->getModel() instanceof OrderInterface
        ;
    }
}