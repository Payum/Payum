<?php

namespace Payum\Core\Security;

use Payum\Core\Exception\LogicException;

class GenericTokenFactory implements GenericTokenFactoryInterface
{
    /**
     * @param array<string, string> $paths
     */
    public function __construct(
        protected TokenFactoryInterface $tokenFactory,
        protected array $paths
    ) {
    }

    /**
     * @param string $gatewayName
     * @param object|array<string, scalar> $model
     * @param string|null $targetPath
     * @param array<string, scalar> $targetParameters
     * @param string|null $afterPath
     * @param array<string, scalar> $afterParameters
     *
     * @return TokenInterface
     */
    public function createToken($gatewayName, $model, $targetPath, array $targetParameters = [], $afterPath = null, array $afterParameters = [])
    {
        return $this->tokenFactory->createToken(
            $gatewayName,
            $model,
            $targetPath,
            $targetParameters,
            $afterPath,
            $afterParameters
        );
    }

    /**
     * @param string $gatewayName
     * @param object|array<string, scalar> $model
     * @param string|null $afterPath
     * @param array<string, scalar> $afterParameters
     *
     * @return TokenInterface
     */
    public function createCaptureToken($gatewayName, $model, $afterPath, array $afterParameters = [])
    {
        $capturePath = $this->getPath('capture');

        $afterToken = $this->createToken($gatewayName, $model, $afterPath ?? $this->getPath('done'), $afterParameters);

        return $this->createToken(
            $gatewayName,
            $model,
            $capturePath,
            [],
            $afterToken->getTargetUrl()
        );
    }

    /**
     * @param string $gatewayName
     * @param object|array<string, scalar> $model
     * @param string|null $afterPath
     * @param array<string, scalar> $afterParameters
     * @param string|null $finalPath Path the $afterPath token itself should redirect to once it
     *                               completes without producing its own reply. Needed when $afterPath
     *                               is itself another Payum action (e.g. chaining authorize into a
     *                               subsequent capture step) to avoid leaving that token without an
     *                               afterUrl of its own, which otherwise fails later with
     *                               "Cannot redirect to an empty URL."
     * @param array<string, scalar> $finalParameters
     *
     * @return TokenInterface
     */
    public function createAuthorizeToken($gatewayName, $model, $afterPath, array $afterParameters = [], $finalPath = null, array $finalParameters = [])
    {
        $authorizePath = $this->getPath('authorize');

        $afterToken = $this->createToken($gatewayName, $model, $afterPath, $afterParameters, $finalPath, $finalParameters);

        return $this->createToken($gatewayName, $model, $authorizePath, [], $afterToken->getTargetUrl());
    }

    /**
     * @param string $gatewayName
     * @param object|array<string, scalar> $model
     * @param string|null $afterPath
     * @param array<string, scalar> $afterParameters
     *
     * @return TokenInterface
     */
    public function createRefundToken($gatewayName, $model, $afterPath = null, array $afterParameters = [])
    {
        $refundPath = $this->getPath('refund');

        $afterUrl = null;
        if ($afterPath) {
            $afterUrl = $this->createToken($gatewayName, $model, $afterPath, $afterParameters)->getTargetUrl();
        }

        return $this->createToken($gatewayName, $model, $refundPath, [], $afterUrl);
    }

    /**
     * @param string $gatewayName
     * @param object|array<string, scalar> $model
     * @param string|null $afterPath
     * @param array<string, scalar> $afterParameters
     *
     * @return TokenInterface
     */
    public function createCancelToken($gatewayName, $model, $afterPath = null, array $afterParameters = [])
    {
        $cancelPath = $this->getPath('cancel');

        $afterUrl = null;
        if ($afterPath) {
            $afterUrl = $this->createToken($gatewayName, $model, $afterPath, $afterParameters)->getTargetUrl();
        }

        return $this->createToken($gatewayName, $model, $cancelPath, [], $afterUrl);
    }

    /**
     * @param string $gatewayName
     * @param object|array<string, scalar> $model
     * @param string|null $afterPath
     * @param array<string, scalar> $afterParameters
     * @param string|null $finalPath Path the $afterPath token itself should redirect to once it
     *                               completes without producing its own reply. See createAuthorizeToken().
     * @param array<string, scalar> $finalParameters
     *
     * @return TokenInterface
     */
    public function createPayoutToken($gatewayName, $model, $afterPath, array $afterParameters = [], $finalPath = null, array $finalParameters = [])
    {
        $capturePath = $this->getPath('payout');

        $afterToken = $this->createToken($gatewayName, $model, $afterPath, $afterParameters, $finalPath, $finalParameters);

        return $this->createToken(
            $gatewayName,
            $model,
            $capturePath,
            [],
            $afterToken->getTargetUrl()
        );
    }

    /**
     * @param string $gatewayName
     * @param object|array<string, scalar> $model
     *
     * @return TokenInterface
     */
    public function createNotifyToken($gatewayName, $model = null)
    {
        return $this->createToken($gatewayName, $model, $this->getPath('notify'));
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function getPath($name)
    {
        if (empty($this->paths[$name])) {
            throw new LogicException(sprintf(
                'The path "%s" is not found. Possible paths are %s',
                $name,
                implode(', ', array_keys($this->paths))
            ));
        }

        return $this->paths[$name];
    }
}
