<?php

declare(strict_types=1);

namespace Payum\Core\Model;

trait CustomerTrait
{
    /**
     * @var array<string, string>
     */
    protected array $gatewayCustomerIds = [];

    public function getGatewayCustomerId(string $gatewayName): ?string
    {
        return $this->gatewayCustomerIds[$gatewayName] ?? null;
    }

    public function setGatewayCustomerId(string $gatewayName, string $id): void
    {
        $this->gatewayCustomerIds[$gatewayName] = $id;
    }
}
