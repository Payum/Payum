<?php
namespace Payum\AuthorizeNet\Aim\Action;

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
        $details['amount'] = $order->getTotalAmount();
        $details['invoice_number'] = $order->getNumber();
        $details['description'] = $order->getDescription();
        $details['email_address'] = $order->getClientEmail();
        $details['customer_id'] = $order->getClientId();

        $order->setDetails($details);
    }
}
