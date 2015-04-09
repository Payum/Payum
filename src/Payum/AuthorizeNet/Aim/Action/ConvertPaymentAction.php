<?php
namespace Payum\AuthorizeNet\Aim\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;

class ConvertPaymentAction implements ActionInterface
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
        $divisor = pow(10, $payment->getCurrencyDigitsAfterDecimalPoint());

        $details = ArrayObject::ensureArrayObject($payment->getDetails());
        $details['amount'] = $payment->getTotalAmount() / $divisor;
        $details['invoice_num'] = $payment->getNumber();
        $details['description'] = $payment->getDescription();
        $details['email'] = $payment->getClientEmail();
        $details['cust_id'] = $payment->getClientId();

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
