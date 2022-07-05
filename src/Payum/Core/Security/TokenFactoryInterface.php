<?php

namespace Payum\Core\Security;

interface TokenFactoryInterface
{
    /**
     * @param string      $gatewayName
     * @param object|null $model
     * @param string      $targetPath
     * @param string      $afterPath
     *
     * @return TokenInterface
     */
    public function createToken($gatewayName, $model, $targetPath, array $targetParameters = [], $afterPath = null, array $afterParameters = []);
}
