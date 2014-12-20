<?php
namespace Payum\Core\Security;

interface GenericTokenFactoryInterface extends TokenFactoryInterface
{
    /**
     * @param string $paymentName
     * @param object $model
     * @param string $afterPath
     * @param array  $afterParameters
     *
     * @return TokenInterface
     */
    public function createAuthorizeToken($paymentName, $model, $afterPath, array $afterParameters = array());

    /**
     * @param string $paymentName
     * @param object $model
     * @param string $afterPath
     * @param array  $afterParameters
     *
     * @return TokenInterface
     */
    public function createCaptureToken($paymentName, $model, $afterPath, array $afterParameters = array());

    /**
     * @param string $paymentName
     * @param object $model
     * @param string $afterPath
     * @param array  $afterParameters
     *
     * @return TokenInterface
     */
    public function createRefundToken($paymentName, $model, $afterPath = null, array $afterParameters = array());

    /**
     * @param string      $paymentName
     * @param object|null $model
     *
     * @return TokenInterface
     */
    public function createNotifyToken($paymentName, $model = null);
}
