<?php
namespace Payum\Core\Action;

use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetCurrency;
use Payum\ISO4217\ISO4217;

class GetCurrencyAction extends GatewayAwareAction
{
    /**
     * @var ISO4217
     */
    protected $iso4217;

    /**
     * @param ISO4217 $iso4217
     */
    public function __construct(ISO4217 $iso4217 = null)
    {
        $this->iso4217 = $iso4217 ?: new ISO4217();
    }

    /**
     * {@inheritDoc}
     *
     * @param GetCurrency $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $currency = is_numeric($request->code) ?
            $this->iso4217->findByNumeric($request->code) :
            $this->iso4217->findByAlpha3($request->code)
        ;

        $request->alpha3 = $currency->getAlpha3();
        $request->country = $currency->getCountry();
        $request->exp = $currency->getExp();
        $request->name = $currency->getName();
        $request->numeric = $currency->getNumeric();
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetCurrency &&
            $request->code
        ;
    }
}
