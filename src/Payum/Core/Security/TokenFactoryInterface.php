<?php

namespace Payum\Core\Security;

interface TokenFactoryInterface
{
    /**
     * @param array<string, ?string> $targetParameters
     * @param array<string, ?string> $afterParameters
     */
    public function createToken(string $gatewayName, ?object $model, string $targetPath, array $targetParameters = [], ?string $afterPath = null, array $afterParameters = []): TokenInterface;
}
