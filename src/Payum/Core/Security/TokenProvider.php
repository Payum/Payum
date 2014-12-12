<?php
namespace Payum\Core\Security;

class TokenProvider implements TokenProviderInterface
{

    /**
     *
     * @var TokenFactoryInterface
     */
    protected $tokenFactory;

    /**
     *
     * @var string
     */
    protected $capturePath;

    /**
     *
     * @var string
     */
    protected $refundPath;

    /**
     *
     * @var string
     */
    protected $notifyPath;

    /**
     *
     * @var string
     */
    protected $authorizePath;

    /**
     *
     * @param TokenFactoryInterface $tokenFactory
     * @param string $capturePath
     * @param string $notifyPath
     * @param string $authorizePath
     * @param string $refundPath
     */
    public function __construct(TokenFactoryInterface $tokenFactory, $capturePath, $notifyPath, $authorizePath, $refundPath)
    {
        $this->tokenFactory = $tokenFactory;

        $this->capturePath = $capturePath;
        $this->refundPath = $refundPath;
        $this->notifyPath = $notifyPath;
        $this->authorizePath = $authorizePath;
    }

    /**
     * {@inheritDoc}
     */
    public function createCaptureToken($paymentName, $model, $afterPath, array $afterParameters = null)
    {
        $afterPath = $this->tokenFactory->createToken($paymentName, $model, $afterPath, $afterParameters)->getTargetUrl();
        $captureToken = $this->tokenFactory->createToken($paymentName, $model, $this->capturePath, null, $afterPath);
        return $captureToken;
    }

    /**
     * {@inheritDoc}
     */
    public function createRefundToken($paymentName, $model, $afterPath = null, array $afterParameters = null)
    {
        $afterPath === null || $afterPath = $this->tokenFactory->createToken($paymentName, $model, $afterPath, $afterParameters)->getTargetUrl();
        $refundToken = $this->tokenFactory->createToken($paymentName, $model, $this->refundPath, null, $afterPath);
        return $refundToken;
    }

    /**
     * {@inheritDoc}
     */
    public function createAuthorizeToken($paymentName, $model, $afterPath, array $afterParameters = null)
    {
        $afterPath = $this->tokenFactory->createToken($paymentName, $model, $afterPath, $afterParameters)->getTargetUrl();
        $authorizeToken = $this->tokenFactory->createToken($paymentName, $model, $this->authorizePath, null, $afterPath);
        return $authorizeToken;
    }

    /**
     * {@inheritDoc}
     */
    public function createNotifyToken($paymentName, $model = null)
    {
        return $this->tokenFactory->createToken($paymentName, $model, $this->notifyPath);
    }
}
