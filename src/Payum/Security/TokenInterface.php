<?php
namespace Payum\Security;

use Payum\Exception\InvalidArgumentException;
use Payum\Model\DetailsAggregateInterface;
use Payum\Model\DetailsAwareInterface;
use Payum\Storage\Identificator;
use Payum\Security\Util\Random;

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