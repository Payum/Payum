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
        $divisor = pow(10, $currency->exp);

        $details = ArrayObject::ensureArrayObject($payment->getDetails());
        $details['amount'] = abs($payment->getTotalAmount());
        $details['invoice_num'] = $payment->getNumber();
        $details['description'] = $payment->getDescription();
        $details['email'] = $payment->getClientEmail();
        $details['cust_id'] = $payment->getClientId();
        $details['card_number'] = $payment->getCreditCardNumber();
        $details['expire_at'] = $payment->getExpireMonth() . $payment->getExpireYear();
        if($payment->getRefund() == 'true'){
            $details['trans_id'] = $payment->getTransactionID();
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
