<?php
namespace Payum\Core\Security;

interface TokenProviderInterface
{
    /**
     * @param string $paymentName
     * @param object $model
     * @param string $afterPath
     * @param array|null $afterParameters
     *
     * @return TokenInterface
     */
    public function createAuthorizeToken($paymentName, $model, $afterPath, array $afterParameters = null);

    /**
     * @param string $paymentName
     * @param object $model
     * @param string $afterPath
     * @param array|null $afterParameters
     *
     * @return TokenInterface
     */
    public function createCaptureToken($paymentName, $model, $afterPath, array $afterParameters = null);

    /**
     * @param string $paymentName
     * @param object $model
     * @param string $afterPath
     * @param array|null $afterParameters
     *
     * @return TokenInterface
     */
    public function createRefundToken($paymentName, $model, $afterPath = null, array $afterParameters = null);

    /**
     * @param string $paymentName
     * @param object|null $model
     *
     * @return TokenInterface
     */
    public function createNotifyToken($paymentName, $model = null);
}
