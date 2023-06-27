<?php

namespace Payum\Core\Action;

use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\ISO4217\Currency;
use Payum\Core\Request\GetCurrency;

class GetCurrencyAction implements ActionInterface
{
    public function execute(mixed $request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $currency = is_numeric($request->code) ?
            Currency::createFromIso4217Numeric($request->code) :
            Currency::createFromIso4217Alpha3($request->code)
        ;

        $request->alpha3 = $currency->getAlpha3();
        $request->country = $currency->getCountry();
        $request->exp = $currency->getExp();
        $request->name = $currency->getName();
        $request->numeric = $currency->getNumeric();
    }

    public function supports(mixed $request): bool
    {
        return $request instanceof GetCurrency &&
            $request->code
        ;
    }
}
