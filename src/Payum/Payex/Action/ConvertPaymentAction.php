<?php
namespace Payum\Payex\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Payex\Api\OrderApi;

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

        $details = ArrayObject::ensureArrayObject($payment->getDetails());
        $details['price'] = $payment->getTotalAmount();
        $details['priceArgList'] = '';
        $details['vat'] = 0;
        $details['currency'] = $payment->getCurrencyCode();
        $details['orderId'] = $payment->getNumber();
        $details['productNumber'] = 'n\a';
        $details['purchaseOperation'] = OrderApi::PURCHASEOPERATION_SALE;
        $details['view'] = OrderApi::VIEW_CREDITCARD;
        $details['description'] = $payment->getDescription();
        $details['clientIdentifier'] = '';
        $details['additionalValues'] = '';
        $details['agreementRef'] = '';
        $details['clientLanguage'] = isset($details['clientLanguage']) ? $details['clientLanguage'] : 'en-US';
        $details['autoPay'] = false;

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
