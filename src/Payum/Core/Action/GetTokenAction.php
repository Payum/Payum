<?php

namespace Payum\Core\Action;

use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetToken;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;

/**
 * @deprecated since 2.0.0, will be removed in 3.0. A handler reads the token this execution came in on
 *             from Payum\Core\Handler\Context::token(); one that needs a different token looks it up in
 *             the token storage, Payum\Core\Payum::getTokenStorage().
 */
class GetTokenAction implements ActionInterface
{
    /**
     * @var StorageInterface<TokenInterface>
     */
    private StorageInterface $tokenStorage;

    /**
     * @param StorageInterface<TokenInterface> $tokenStorage
     */
    public function __construct(StorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param GetToken $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        // By hash, the way HttpRequestVerifier does it. An Identity naming TokenInterface finds nothing
        // in a storage built on the concrete Token class, which is every storage Payum ships.
        if (! $token = $this->tokenStorage->find($request->getHash())) {
            throw new LogicException(sprintf('The token %s could not be found', $request->getHash()));
        }

        $request->setToken($token);
    }

    public function supports($request): bool
    {
        return $request instanceof GetToken;
    }
}
