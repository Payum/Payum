<?php
namespace Payum\Be2Bill\Action;

use Payum\Core\Action\BaseCaptureOrderAction;
use Payum\Core\Model\OrderInterface;
use Payum\Core\Security\TokenInterface;

class CaptureOrderAction extends BaseCaptureOrderAction
{
    /**
     * {@inheritDoc}
     */
    protected function composeDetails(OrderInterface $order, TokenInterface $token = null)
    {
        $details = $order->getDetails();
        $details['ORDERID'] = $order->getNumber();
        $details['DESCRIPTION'] = $order->getDescription();
        $details['AMOUNT'] = $order->getTotalAmount();
        $details['CLIENTIDENT'] = $order->getClientId();
        $details['CLIENTEMAIL'] = $order->getClientEmail();

        $order->setDetails($details);
    }
}
