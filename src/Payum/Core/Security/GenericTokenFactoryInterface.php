<?php

namespace Payum\Core\Security;

/**
 * @deprecated since 2.0, removed in 3.0. Use {@see \Payum\Core\Security\TokenIssuerInterface} instead.
 */
interface GenericTokenFactoryInterface extends TokenFactoryInterface
{
    /**
     * @param string $gatewayName
     * @param object $model
     * @param string $afterPath
     *
     * @return TokenInterface
     */
    public function createAuthorizeToken($gatewayName, $model, $afterPath, array $afterParameters = []);

    /**
     * @param string $gatewayName
     * @param object $model
     * @param string $afterPath
     *
     * @return TokenInterface
     */
    public function createCaptureToken($gatewayName, $model, $afterPath, array $afterParameters = []);

    /**
     * @param string $gatewayName
     * @param object $model
     * @param string $afterPath
     *
     * @return TokenInterface
     */
    public function createRefundToken($gatewayName, $model, $afterPath = null, array $afterParameters = []);

    /**
     * @param string $gatewayName
     * @param object $model
     * @param string $afterPath
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
