<?php
namespace Payum\AuthorizeNet\Aim\Action;

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
        $divisor = pow(10, $order->getCurrencyDigitsAfterDecimalPoint());

        $details = $order->getDetails();
        $details['amount'] = $order->getTotalAmount() / $divisor;
        $details['invoice_num'] = $order->getNumber();
        $details['description'] = $order->getDescription();
        $details['email'] = $order->getClientEmail();
        $details['cust_id'] = $order->getClientId();

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
