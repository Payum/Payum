<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

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
        $divisor = pow(10, $order->getTotalPrice()->getCurrency()->getDigitsAfterDecimalPoint());

        $details = $order->getDetails();
        $details['PAYMENTREQUEST_0_CURRENCYCODE'] = $order->getTotalPrice()->getCurrency()->getCode();
        $details['PAYMENTREQUEST_0_AMT'] = $order->getTotalPrice()->getAmount() / $divisor;

        $order->setDetails($details);
    }
}
