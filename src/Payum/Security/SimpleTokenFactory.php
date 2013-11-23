<?php
namespace Payum\Security;

use Payum\Exception\InvalidArgumentException;
use Payum\Registry\RegistryInterface;
use Payum\Storage\StorageInterface;

class SimpleTokenFactory extends AbstractTokenFactory
{
    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @param string $baseUrl
     * @param \Payum\Storage\StorageInterface $tokenStorage
     * @param \Payum\Registry\RegistryInterface $payum
     */
    public function __construct($baseUrl, StorageInterface $tokenStorage, RegistryInterface $payum)
    {
        parent::__construct($tokenStorage, $payum);

        $this->baseUrl = $baseUrl;
    }

    /**
     * @param string $paymentName
     * @param object $model
     * @param string $afterPath
     * @param array $afterParameters
     *
     * @return TokenInterface
     */
    public function createCaptureToken($paymentName, $model, $afterPath, array $afterParameters = array())
    {
        $afterToken = $this->createToken($paymentName, $model, $afterPath, $afterParameters);

        $captureToken = $this->createToken($paymentName, $model, 'capture.php');
        $captureToken->setAfterUrl($afterToken->getTargetUrl());

        $this->tokenStorage->updateModel($captureToken);

        return $captureToken;
    }

    /**
     * {@inheritDoc}
     */
    protected function generateUrl($path, array $parameters = array())
    {
        $url = $this->baseUrl.$path;

        if (false == empty($parameters)) {
            $url .= '?'.http_build_query($parameters);
        }

        return $url;
    }
}