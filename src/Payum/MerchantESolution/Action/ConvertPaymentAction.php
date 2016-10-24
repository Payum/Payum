<?php

namespace Payum\MerchantESolution\Action;

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
        $payment = $request->getSource();
        $details = ArrayObject::ensureArrayObject($payment->getDetails());
        $details['description'] = $payment->getDescription();
        $details['transaction_amount'] = $payment->getTotalAmount();
        $details["currency"] = $payment->getCurrencyCode();
        $details["cardName"] = $payment->getCardName();
        $details["fname"] = $payment->getFirstName();
        $details["lname"] = $payment->getLastName();
        $details["addressLine1"] = $payment->getAddressLine1();
        $details["addressZip"] = $payment->getAddressZip();
        $details['addressState'] = $payment->getAddressState();
        $details["orderno"] = $payment->getOrderNumber();
        $details["eventstatus"] = $payment->getEventStatus();
        $details["env"] = $payment->getEnvironment();
        $details["refund"] = $payment->getRefund();
        $details["transaction_id"] = $payment->getTransactionID();
        $details["card_type"]=$payment->getCardType();
        $card = $payment->getCreditCard();
        $details['card_number'] = $card->getNumber();
        $details['exp_date'] = $payment->getExpireMonth().$payment->getExpireYear();
        $details['cvc'] = $card->getSecurityCode();
        $details['transaction_type']= $payment->getRefund() == "true" ? "U" : "D";

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