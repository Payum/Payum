<?php
namespace Payum\Core\Action;

use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetCurrency;
use Payum\Core\ISO4217\Currency;
use Payum\ISO4217\ISO4217;

class GetCurrencyAction implements ActionInterface
{
    /**
     * @var ISO4217
     * @deprecated The iso4217 property is deprecated and will be removed in v2
     */
    protected $iso4217;

    private $usePayumIso4217 = false;

    /**
     * @param ISO4217 $iso4217
     */
    public function __construct(ISO4217 $iso4217 = null)
    {
        if ($iso4217 instanceof ISO4217) {
            @trigger_error(sprintf('Passing an instance of %s in %s is deprecated and won\'t be supported in version 2.', ISO4217::class, __METHOD__), E_USER_DEPRECATED);
            $this->usePayumIso4217 = true;
        }

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

        if ($this->usePayumIso4217) {
            $currency = is_numeric($request->code) ?
                $this->iso4217->findByNumeric($request->code) :
                $this->iso4217->findByAlpha3($request->code)
            ;
        } else {
            $currency = is_numeric($request->code) ?
                Currency::createFromIso4217Numeric($request->code) :
                Currency::createFromIso4217Alpha3($request->code)
            ;
        }


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
