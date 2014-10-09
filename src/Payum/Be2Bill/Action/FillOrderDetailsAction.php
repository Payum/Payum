<?php
namespace Payum\Be2Bill\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\FillOrderDetails;

class FillOrderDetailsAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param FillOrderDetails $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $order = $request->getOrder();

        $details = $order->getDetails();
        $details['ORDERID'] = $order->getNumber();
        $details['DESCRIPTION'] = $order->getDescription();
        $details['AMOUNT'] = $order->getTotalAmount();
        $details['CLIENTIDENT'] = $order->getClientId();
        $details['CLIENTEMAIL'] = $order->getClientEmail();

        $order->setDetails($details);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof FillOrderDetails;
    }
}
