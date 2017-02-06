<?php
namespace Payum\Core\Action;

use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetToken;
use Payum\Core\Storage\StorageInterface;

class GetTokenAction extends GatewayAwareAction
{
    /**
     * @var StorageInterface
     */
    private $tokenStorage;

    /**
     * @param StorageInterface $tokenStorage
     */
    public function __construct(StorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritDoc}
     *
     * @param $request GetToken
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        if (false == $token = $this->tokenStorage->find($request->getHash())) {
            throw new LogicException(sprintf('The token %s could not be found', $request->getHash()));
        }

        $request->setToken($token);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof GetToken;
    }
}
