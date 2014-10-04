<?php
namespace Payum\AuthorizeNet\Aim\Action;

use Payum\Core\Action\BaseCaptureOrderAction;
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
        $details = $order->getDetails();
        $details['amount'] = $order->getTotalPrice()->getAmount();

        $order->setDetails($details);
    }
}
