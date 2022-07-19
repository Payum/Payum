<?php

namespace Payum\Core\Request;

use Payum\Core\Security\TokenInterface;

class GetToken
{
    private string $hash;

    private ?TokenInterface $token = null;

    /**
     * @param string $hash
     */
    public function __construct($hash)
    {
        $this->hash = $hash;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getToken(): ?TokenInterface
    {
        return $this->token;
    }

    public function setToken(TokenInterface $token): void
    {
        $this->token = $token;
    }
}
