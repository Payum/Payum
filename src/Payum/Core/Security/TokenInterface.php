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
    public function getHash(): string;

    public function setHash(string $hash): void;

    public function getTargetUrl(): ?string;

    public function setTargetUrl(?string $targetUrl): void;

    public function getAfterUrl(): string;

    public function setAfterUrl(string $afterUrl): void;

    public function getGatewayName(): string;

    public function setGatewayName(string $gatewayName): void;
}
