<?php
namespace Payum\Core\Model;

/**
 * @method array getDetails()
 */
interface PayoutInterface extends DetailsAggregateInterface, DetailsAwareInterface
{
    /**
     * @return string
     */
    public function getRecipientId();

    /**
     * @return string
     */
    public function getRecipientEmail();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return int
     */
    public function getTotalAmount();

    /**
     * @return string
     */
    public function getCurrencyCode();
}
