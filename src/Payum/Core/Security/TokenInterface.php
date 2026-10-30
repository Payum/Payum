<?php

namespace Payum\Core\Security;

use Payum\Core\Model\DetailsAwareInterface;
use Payum\Core\Storage\IdentityInterface;

/**
 * @method IdentityInterface getDetails()
 */
interface TokenInterface extends PaymentGrantInterface, DetailsAwareInterface
{
    /**
     * @param string $hash
     */
    public function setHash($hash);

    /**
     * @return string
     */
    public function getTargetUrl();

    /**
     * @param string $targetUrl
     */
    public function setTargetUrl($targetUrl);

    /**
     * @return string
     */
    public function getAfterUrl();

    /**
     * @param string $afterUrl
     */
    public function setAfterUrl($afterUrl);

    /**
     * @param string $gatewayName
     */
    public function setGatewayName($gatewayName);
}
