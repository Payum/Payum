<?php

declare(strict_types=1);

namespace Payum\Core\Model;

interface CustomerAwareInterface
{
    public function getCustomer(): ?CustomerInterface;
}
