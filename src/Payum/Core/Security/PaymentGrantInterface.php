<?php

declare(strict_types=1);

namespace Payum\Core\Security;

use Payum\Core\Model\DetailsAggregateInterface;

/**
 * What a token grants: one gateway, acting on one subject. A client with no browser holds this and has
 * no use for the URLs on {@see TokenInterface}.
 */
interface PaymentGrantInterface extends DetailsAggregateInterface
{
    /**
     * @return string
     */
    public function getHash();

    /**
     * @return string
     */
    public function getGatewayName();
}
