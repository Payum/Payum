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
     * @return string
     */
    function getDescription();

    /**
     * @return string
     */
    function getClientEmail();

    /**
     * @return string
     */
    function getClientId();

    /**
     * @return int
     */
    function getTotalAmount();

    /**
     * @return string
     */
    function getCurrencyCode();

    /**
     * @return int
     */
    function getCurrencyDigitsAfterDecimalPoint();
}
