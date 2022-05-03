<?php
namespace Payum\Core\Security;

/**
 * @deprecated since 1.3.7 and will be removed in 2.0
 */
interface GenericTokenFactoryInterface extends TokenFactoryInterface
{
    public function createAuthorizeToken(string $gatewayName, object $model, string $afterPath, array $afterParameters = []): TokenInterface;

    function createCaptureToken(string $gatewayName, object $model, string $afterPath, array $afterParameters = []): TokenInterface;

    public function createRefundToken(string $gatewayName, object $model, string $afterPath = null, array $afterParameters = []): TokenInterface;

    public function createPayoutToken(string $gatewayName, object $model, string $afterPath, array $afterParameters = []): TokenInterface;

    public function createNotifyToken(string $gatewayName, object $model = null): TokenInterface;
}
