<?php
namespace Payum\Offline\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\FillOrderDetails;
use Payum\Offline\Constants;

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
        $details['amount'] = $order->getTotalAmount();
        $details['currency'] = $order->getCurrencyCode();
        $details['number'] = $order->getNumber();
        $details['description'] = $order->getDescription();
        $details['client_email'] = $order->getClientEmail();
        $details['client_id'] = $order->getClientId();
        $details[Constants::FIELD_PAID] = true;

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
