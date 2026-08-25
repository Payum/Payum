<?php

namespace Payum\Core\Request;

use Payum\Core\Security\TokenInterface;

/**
 * @deprecated since 2.0.0, will be removed in 3.0. A handler reads the token this execution came in on
 *             from Payum\Core\Handler\Context::token(); one that needs a different token looks it up in
 *             the token storage, Payum\Core\Payum::getTokenStorage().
 */
class GetToken
{
    /**
     * @var string
     */
    private $hash;

    private ?TokenInterface $token = null;

    /**
     * @param string $hash
     */
    public function __construct($hash)
    {
        $this->hash = $hash;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @return TokenInterface
     */
    public function getToken()
    {
        return $this->token;
    }

    public function setToken(TokenInterface $token): void
    {
        $this->token = $token;
    }
}
