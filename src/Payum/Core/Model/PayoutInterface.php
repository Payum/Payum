<?php

namespace Payum\Core\Model;

/**
 * @method array getDetails()
 */
interface PayoutInterface extends SubjectInterface
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
     * @return int|null minor units
     */
    public function getTotalAmount();

    /**
     * @return string|null ISO 4217 alpha-3, or a code registered on the gateway's Money\Currencies
     */
    public function getCurrencyCode();
}
