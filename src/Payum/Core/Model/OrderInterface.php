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
    public function getNumber();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return string
     */
    public function getClientEmail();

    /**
     * @return string
     */
    public function getClientId();

    /**
     * @return int
     */
    public function getTotalAmount();

    /**
     * @return string
     */
    public function getCurrencyCode();

    /**
     * @return int
     */
    public function getCurrencyDigitsAfterDecimalPoint();
}
