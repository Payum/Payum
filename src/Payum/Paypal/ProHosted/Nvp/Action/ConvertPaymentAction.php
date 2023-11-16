<?php

namespace Payum\Paypal\ProHosted\Nvp\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetCurrency;

class ConvertPaymentAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @param Convert $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        $this->gateway->execute($currency = new GetCurrency($payment->getCurrencyCode()));

        $details = ArrayObject::ensureArrayObject($payment->getDetails());
        $details['INVNUM'] = $payment->getNumber();
        $details['AMT'] = (float) $payment->getTotalAmount();
        $details['CURRENCYCODE'] = $payment->getCurrencyCode();

        $request->setResult((array) $details);
    }

    public function supports($request)
    {
        return $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            'array' == $request->getTo();
    }
}
