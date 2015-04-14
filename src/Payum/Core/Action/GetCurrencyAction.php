<?php
namespace Payum\Core\Action;

use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\GetCurrency;
use Payum\ISO4217\ISO4217;

class GetCurrencyAction extends GatewayAwareAction
{
    protected $iso4217;

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

        /** @var $payment PaymentInterface */
        $currency = $request->getCode();

        $request->setCurrency(
            is_numeric($currency) ? $this->iso4217->findByNumeric($currency) : $this->iso4217->findByAlpha3($currency)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof GetCurrency;
    }
}
