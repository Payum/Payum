<?php
namespace Payum\Core\Security;

interface GenericTokenFactoryInterface extends TokenFactoryInterface
{
    /**
     * @param string $gatewayName
     * @param object $model
     * @param string $afterPath
     * @param array  $afterParameters
     *
     * @return TokenInterface
     */
    public function createAuthorizeToken($gatewayName, $model, $afterPath, array $afterParameters = array());

    /**
     * @param string $gatewayName
     * @param object $model
     * @param string $afterPath
     * @param array  $afterParameters
     *
     * @return TokenInterface
     */
    public function createCaptureToken($gatewayName, $model, $afterPath, array $afterParameters = array());

    /**
     * @param string $gatewayName
     * @param object $model
     * @param string $afterPath
     * @param array  $afterParameters
     *
     * @return TokenInterface
     */
    public function createRefundToken($gatewayName, $model, $afterPath = null, array $afterParameters = array());

    /**
     * @param string      $gatewayName
     * @param object|null $model
     *
     * @return TokenInterface
     */
    public function createNotifyToken($gatewayName, $model = null);
}
