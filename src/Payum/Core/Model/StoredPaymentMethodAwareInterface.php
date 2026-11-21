<?php

declare(strict_types=1);

namespace Payum\Core\Model;

interface StoredPaymentMethodAwareInterface
{
    public function getStoredPaymentMethod(): ?StoredPaymentMethodInterface;
}
