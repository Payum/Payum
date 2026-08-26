<?php

namespace Payum\Core\Action;

use DomainException;
use Money\Currencies;
use Money\Currency as MoneyCurrency;
use OutOfBoundsException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\ISO4217\Currency;
use Payum\Core\Request\GetCurrency;
use function is_numeric;

class GetCurrencyAction implements ActionInterface
{
    /**
     * @param Currencies|null $currencies answers for codes ISO 4217 does not list — a crypto currency,
     *                                    whose subunit is 8 or 18 rather than 2. Only the exponent and
     *                                    the code come from it; there is no name or country to give
     */
    public function __construct(
        private readonly ?Currencies $currencies = null
    ) {
    }

    /**
     * @param GetCurrency $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $currency = $this->find((string) $request->code);

        $request->alpha3 = $currency->getAlpha3();
        $request->country = $currency->getCountry();
        $request->exp = $currency->getExp();
        $request->name = $currency->getName();
        $request->numeric = $currency->getNumeric();
    }

    public function supports($request)
    {
        return $request instanceof GetCurrency &&
            $request->code
        ;
    }

    private function find(string $code): Currency
    {
        try {
            return is_numeric($code) ?
                Currency::createFromIso4217Numeric($code) :
                Currency::createFromIso4217Alpha3($code)
            ;
        } catch (DomainException|OutOfBoundsException $e) {
            $money = new MoneyCurrency($code);

            if (! $this->currencies instanceof Currencies || ! $this->currencies->contains($money)) {
                throw $e;
            }

            return new Currency($code, $code, '', $this->currencies->subunitFor($money), []);
        }
    }
}
