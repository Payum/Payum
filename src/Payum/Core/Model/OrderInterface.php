<?php
namespace Payum\Core\Model;

interface OrderInterface extends DetailsAggregateInterface, DetailsAwareInterface
{
    /**
     * @return string
     */
    function getNumber();

    /**
     * @return int
     */
    function getTotalAmount();

    /**
     * @return string
     */
    function getTotalCurrency();
}
