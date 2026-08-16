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
     * @param string|null $finalPath Path the $afterPath token itself should redirect to once it
     *                               completes without producing its own reply. Needed when $afterPath
     *                               is itself another Payum action (e.g. chaining authorize into a
     *                               subsequent capture step) to avoid leaving that token without an
     *                               afterUrl of its own.
     * @param array<string, scalar> $finalParameters
     *
     * @return TokenInterface
     */
    public function createAuthorizeToken($gatewayName, $model, $afterPath, array $afterParameters = [], $finalPath = null, array $finalParameters = []);

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
     * @param string|null $finalPath Path the $afterPath token itself should redirect to once it
     *                               completes without producing its own reply. See createAuthorizeToken().
     * @param array<string, scalar> $finalParameters
     *
     * @return TokenInterface
     */
    public function createPayoutToken($gatewayName, $model, $afterPath, array $afterParameters = [], $finalPath = null, array $finalParameters = []);

    /**
     * @param string      $gatewayName
     * @param object|null $model
     *
     * @return TokenInterface
     */
    public function createNotifyToken($gatewayName, $model = null);
}
