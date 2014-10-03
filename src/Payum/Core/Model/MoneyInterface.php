<?php
namespace Payum\Core\Model;

interface MoneyInterface
{
    /**
     * @return int
     */
    function getAmount();

    /**
     * @return CurrencyInterface
     */
    function getCurrency();
} 