<?php
namespace Payum\AuthorizeNet\Aim\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetCurrency;

class ConvertPaymentAction extends GatewayAwareAction
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

        $this->gateway->execute($currency = new GetCurrency($payment->getCurrencyCode()));
        $divisor = pow(10, $currency->getIso4217()->getExp());

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
