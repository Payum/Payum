<?php
namespace Payum\Be2Bill\Action;

use Payum\Core\Action\BaseCaptureOrderAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Model\OrderInterface;
use Payum\Core\Security\TokenInterface;

class CaptureOrderAction extends BaseCaptureOrderAction
{
    /**
     * @param OrderInterface $order
     * @param TokenInterface $token
     */
    protected function composeDetails(OrderInterface $order, TokenInterface $token = null)
    {
        $details = ArrayObject::ensureArrayObject($order->getDetails());
        $details['AMOUNT'] = $order->getTotalPrice()->getAmount();
        $details['ORDERID'] = $order->getNumber();

        $details['CLIENTIDENT'] || $details['CLIENTIDENT'] = $order->getClient()->getEmail();
        $details['CLIENTEMAIL'] || $details['CLIENTEMAIL'] = $order->getClient()->getEmail();
        $details['DESCRIPTION'] || $details['DESCRIPTION'] = sprintf(
            'An order %s for a client %s',
            $order->getNumber(),
            $order->getClient()->getEmail()
        );

        $order->setDetails($details);
    }
}
