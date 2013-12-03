<?php
namespace Payum\Core\Security;

use Payum\Core\Model\DetailsAggregateInterface;
use Payum\Core\Model\DetailsAwareInterface;

interface TokenInterface extends DetailsAggregateInterface, DetailsAwareInterface
{
    /**
     * @return string
     */
    function getHash();

    /**
     * @param string $hash
     */
    function setHash($hash);

    /**
     * @return string
     */
    function getTargetUrl();

    /**
     * @param string $targetUrl
     */
    function setTargetUrl($targetUrl);

    /**
     * @return string
     */
    function getAfterUrl();

    /**
     * @param string $afterUrl
     */
    function setAfterUrl($afterUrl);

    /**
     * @return string
     */
    function getPaymentName();

    /**
     * @param string $paymentName
     */
    function setPaymentName($paymentName);
}