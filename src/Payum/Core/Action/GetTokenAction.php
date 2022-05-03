<?php
namespace Payum\Core\Action;

use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetToken;
use Payum\Core\Storage\StorageInterface;

class GetTokenAction implements ActionInterface
{
    public function __construct(private StorageInterface $tokenStorage)
    {}

    /**
     * {@inheritDoc}
     *
     * @param $request GetToken
     */
    public function execute($request): void
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
    public function supports($request): bool
    {
        return $request instanceof GetToken;
    }
}
