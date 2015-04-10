<?php
namespace Payum\Core\Action;

use Alcohol\ISO4217;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\GetCurrency;

class GetCurrencyAction extends GatewayAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param GetCurrency $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var $payment PaymentInterface */
        $currency = $request->getCode();

        $request->setIso4217(is_numeric($currency) ? ISO4217::findByNumeric($currency) : ISO4217::findByAlpha3($currency));
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof GetCurrency;
    }
}
