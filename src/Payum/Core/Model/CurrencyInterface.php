<?php
namespace Payum\Core\Model;

interface CurrencyInterface
{
    /**
     * @return string
     */
    function getCode();

    /**
     * @return int
     */
    function getDigitsAfterDecimalPoint();
}
