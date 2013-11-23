<?php
namespace Payum\Security;

interface TokenFactoryInterface
{
    /**
     * @param string $paymentName
     * @param object $model
     * @param string $targetPath
     * @param array $targetParameters
     * @param string $afterPath
     * @param array $afterParameters
     *
     * @return TokenInterface
     */
    function createToken($paymentName, $model, $targetPath, array $targetParameters = array(), $afterPath = null, array $afterParameters = array());
} 