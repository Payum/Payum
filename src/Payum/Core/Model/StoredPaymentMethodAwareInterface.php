<?php

declare(strict_types=1);

namespace Payum\Core\Model;

interface StoredPaymentMethodAwareInterface
{
    /**
     * A method without a reference is saved during the charge; one with a reference is charged.
     */
    public function getStoredPaymentMethod(): ?StoredPaymentMethodInterface;
}
