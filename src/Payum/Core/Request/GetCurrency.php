<?php
namespace Payum\Core\Request;

use Payum\ISO4217\Currency;

class GetCurrency
{
    /**
     * @var string
     */
    protected $code;

    protected $currency;

    /**
     * @param string|int $code
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * @return string|int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param Currency $currency
     */
    public function setCurrency(Currency $currency)
    {
        $this->currency = $currency;
    }
}