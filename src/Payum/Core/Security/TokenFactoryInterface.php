<?php
namespace Payum\Core\Security;

interface TokenFactoryInterface
{
    /**
     * @param string $paymentName
     * @param object|null $model
     * @param string $targetPath
     * @param array|null $targetParameters
     * @param string|null $afterPath
     * @param array|null $afterParameters
     *
     * @return TokenInterface
     */
    function createToken($paymentName, $model, $targetPath, array $targetParameters = null, $afterPath = null, array $afterParameters = null);
}
