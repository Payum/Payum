<?php
namespace Payum\Core\Security;

/**
 * @deprecated since 1.3.7 and will be removed in 2.0
 */
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
    public function createAuthorizeToken($gatewayName, $model, $afterPath, array $afterParameters = []);

    /**
     * @param string $gatewayName
     * @param object $model
     * @param string $afterPath
     * @param array  $afterParameters
     *
     * @return TokenInterface
     */
    public function createCaptureToken($gatewayName, $model, $afterPath, array $afterParameters = []);

    /**
     * @param string $gatewayName
     * @param object $model
     * @param string $afterPath
     * @param array  $afterParameters
     *
     * @return TokenInterface
     */
    public function createRefundToken($gatewayName, $model, $afterPath = null, array $afterParameters = []);

    /**
     * @param string $gatewayName
     * @param object $model
     * @param string $afterPath
     * @param array  $afterParameters
     *
     * @return TokenInterface
     */
    public function createPayoutToken($gatewayName, $model, $afterPath, array $afterParameters = []);

    /**
     * @param string      $gatewayName
     * @param object|null $model
     *
     * @return TokenInterface
     */
    public function createNotifyToken($gatewayName, $model = null);
}
