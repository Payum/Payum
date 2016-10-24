<?php

namespace Payum\Tranzila\Action;

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
        $details['sale_price'] = abs($payment->getTotalAmount()* 100);
        $details["currency"] = $payment->getCurrencyCode();
        $details["cardName"] = $payment->getCardName();
        $details["buyer_name"] = $payment->getFirstName().''.$payment->getLastName();
        $details["buyer_email"] = $payment->getClientEmail();
        $details["buyer_phone"] = $payment->getWorkPhone();
        $details["addressLine1"] = $payment->getAddressLine1();
        $details["addressZip"] = $payment->getAddressZip();
        $details['addressState'] = $payment->getAddressState();
        $details["transaction_id"] = str_pad($payment->getEventID(), 6, "0", STR_PAD_LEFT) . "-" . str_pad($payment->getAttendeeID(), 8, "0", STR_PAD_LEFT);
        $details["eventstatus"] = $payment->getEventStatus();
        $details["env"] = $payment->getEnvironment();
        $details["product_name"] = $payment->getEventName();
        $details["refund"] = $payment->getRefund();
        $details["id"] = $payment->getTransactionID();
        $details["attendeeid"] = $payment->getAttendeeID();
        $details["buyer_social_id"] = $payment->getIsraeliSocialId();
        $card = $payment->getCreditCard();
        $details['credit_card_number'] = $card->getNumber();
        $details['credit_card_exp'] = $payment->getExpireMonth(). substr($payment->getExpireYear(), 2, 2);
        $details['credit_card_cvv'] = $card->getSecurityCode();

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