<?php
namespace Payum\Core\Model;

class Money implements MoneyInterface
{
    /**
     * @var int
     */
    protected $amount;

    /**
     * @var CurrencyInterface
     */
    protected $currency;

    /**
     * @param int $amount
     * @param CurrencyInterface $currency
     */
    public function __construct($amount = 0, CurrencyInterface $currency = null)
    {
        $this->amount = $amount;
        $this->currency = $currency ?: new Currency('');
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return CurrencyInterface
     */
    public function getCurrency()
    {
        return $this->currency;
    }
}

