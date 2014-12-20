<?php
namespace Payum\Core\Security;

interface TokenFactoryInterface
{
    /**
     * @param string      $paymentName
     * @param object|null $model
     * @param string      $targetPath
     * @param array       $targetParameters
     * @param string      $afterPath
     * @param array       $afterParameters
     *
     * @return TokenInterface
     */
    public function createToken($paymentName, $model, $targetPath, array $targetParameters = array(), $afterPath = null, array $afterParameters = array());
}
