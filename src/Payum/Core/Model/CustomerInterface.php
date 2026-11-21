<?php

declare(strict_types=1);

namespace Payum\Core\Model;

interface CustomerInterface extends DetailsAggregateInterface, DetailsAwareInterface
{
    public function getCustomerId(): string;

    public function getEmail(): ?string;

    public function getName(): ?string;

    public function getGatewayCustomerId(string $gatewayName): ?string;

    public function setGatewayCustomerId(string $gatewayName, string $id): void;
}
