<?php
namespace Payum\Payex\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\FillOrderDetails;
use Payum\Payex\Api\OrderApi;

class FillOrderDetailsAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param FillOrderDetails $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $order = $request->getOrder();

        $details = $order->getDetails();
        $details['price'] = $order->getTotalAmount();
        $details['priceArgList'] = '';
        $details['vat'] = 0;
        $details['currency'] = $order->getCurrencyCode();
        $details['orderId'] = $order->getNumber();
        $details['productNumber'] = 'n\a';
        $details['purchaseOperation'] = OrderApi::PURCHASEOPERATION_SALE;
        $details['view'] = OrderApi::VIEW_CREDITCARD;
        $details['description'] = $order->getDescription();
        $details['clientIdentifier'] = '';
        $details['additionalValues'] = '';
        $details['agreementRef'] = '';
        $details['clientLanguage'] = isset($details['clientLanguage']) ? $details['clientLanguage'] : 'en-US';
        $details['autoPay'] = false;
        $order->setDetails($details);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof FillOrderDetails;
    }
}
