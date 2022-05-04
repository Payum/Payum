<?php
namespace Payum\Core\Security;

interface TokenFactoryInterface
{
    /**
     * @param string      $gatewayName
     * @param object|null $model
     * @param string      $targetPath
     * @param array       $targetParameters
     * @param string      $afterPath
     * @param array       $afterParameters
     *
     * @return TokenInterface
     */
    public function createToken($gatewayName, $model, $targetPath, array $targetParameters = array(), $afterPath = null, array $afterParameters = array());
}
