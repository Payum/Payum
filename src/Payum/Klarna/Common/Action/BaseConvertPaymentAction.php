<?php
namespace Payum\Klarna\Common\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;

abstract class BaseConvertPaymentAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param Convert $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();
        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        if ('SEK' == $payment->getCurrencyCode()) {
            $details['order_lines'] = [[
                'reference' => $payment->getNumber(),
                'name' => $payment->getNumber(),
                'quantity' => 1,
                'unit_price' => $payment->getTotalAmount(),
                'total_amount' => $payment->getTotalAmount(),
                'tax_rate' => 2500
            ]];

            $details['purchase_country'] = 'SE';
            $details['purchase_currency'] = 'SEK';
            $details['locale'] = 'sv-se';
            $details['order_amount'] = $payment->getTotalAmount();
            $details['order_tax_amount'] = $payment->getTotalAmount() * 0.20;
            $details['merchant_reference1'] = $payment->getNumber();
        }

        $request->setResult((array) $details);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            $request->getTo() == 'array'
        ;
    }
}
