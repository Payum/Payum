<?php
namespace Payum\Core\Model;

/**
 * @method array getDetails
 */
interface OrderInterface extends DetailsAggregateInterface, DetailsAwareInterface
{
    /**
     * @return string
     */
    function getNumber();

    /**
     * @return MoneyInterface
     */
    function getTotalPrice();
} 