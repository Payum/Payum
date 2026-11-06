<?php

declare(strict_types=1);

namespace Payum\Skeleton;

use Payum\Core\Config\GatewayConfig;

final class SkeletonConfig implements GatewayConfig
{
    public function __construct(
        public readonly bool $sandbox = true,
    ) {
    }

    public function getGatewayClass(): string
    {
        return SkeletonGateway::class;
    }

    public function baseUrl(): string
    {
        return $this->sandbox ? 'https://sandbox.example.com' : 'https://example.com';
    }
}
