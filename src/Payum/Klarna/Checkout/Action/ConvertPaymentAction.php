<?php

namespace Payum\Klarna\Checkout\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;

class ConvertPaymentAction implements ActionInterface
{
    /**
     * @param Convert $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();
        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        if ('SEK' === $payment->getCurrencyCode()) {
            $details['cart'] = [
                'items' => [[
                    'reference' => $payment->getNumber(),
                    'name' => $payment->getNumber(),
                    'quantity' => 1,
                    // klarna calculate the tax later.
                    'unit_price' => round($payment->getTotalAmount() / 0.75),
                    'discount_rate' => 0,
                    'tax_rate' => 2500,
                ]],
            ];

            $details['purchase_country'] = 'SE';
            $details['purchase_currency'] = 'SEK';
            $details['locale'] = 'sv-se';
        }

        $request->setResult((array) $details);
    }

    public function supports($request)
    {
        return $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            'array' === $request->getTo()
        ;
    }
}
