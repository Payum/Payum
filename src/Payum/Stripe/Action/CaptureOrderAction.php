<?php
namespace Payum\Stripe\Action;

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

        $details["amount"] = $order->getTotalAmount();
        $details["currency"] = $order->getCurrencyCode();
        $details["description"] = $order->getDescription();

        $order->setDetails($details);
    }
}
