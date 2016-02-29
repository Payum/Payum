<?php
namespace Payum\Core\Security;

use Payum\Core\Model\DetailsAggregateInterface;
use Payum\Core\Model\DetailsAwareInterface;
use Payum\Core\Storage\IdentityInterface;

/**
 * @method IdentityInterface getDetails()
 */
interface TokenInterface extends DetailsAggregateInterface, DetailsAwareInterface
{
    /**
     * @return string
     */
    public function getHash();

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
     * @return string
     */
    public function getGatewayName();

    /**
     * @param string $gatewayName
     */
    public function setGatewayName($gatewayName);
}
