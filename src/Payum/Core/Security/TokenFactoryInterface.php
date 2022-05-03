<?php
namespace Payum\Core\Security;

interface TokenFactoryInterface
{
    public function createToken(string $gatewayName,
                                ?object $model,
                                string  $targetPath,
                                array $targetParameters = [],
                                string $afterPath = null,
                                array $afterParameters = []
    ): TokenInterface;
}
