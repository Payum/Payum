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
     * @return ClientInterface
     */
    function getClient();

    /**
     * @return MoneyInterface
     */
    function getTotalPrice();
}
