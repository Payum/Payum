<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

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
        $divisor = pow(10, $order->getCurrencyDigitsAfterDecimalPoint());

        $details = $order->getDetails();
        $details['INVNUM'] = $order->getNumber();
        $details['PAYMENTREQUEST_0_CURRENCYCODE'] = $order->getCurrencyCode();
        $details['PAYMENTREQUEST_0_AMT'] = $order->getTotalAmount() / $divisor;

        $order->setDetails($details);
    }
}
