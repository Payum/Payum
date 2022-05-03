<?php
namespace Payum\Core\Request;

use Payum\Core\Security\TokenInterface;

class GetToken
{
    private string $hash;

    private TokenInterface $token;

    public function __construct(string $hash)
    {
        $this->hash = $hash;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getToken(): TokenInterface
    {
        return $this->token;
    }

    public function setToken(TokenInterface $token)
    {
        $this->token = $token;
    }
}
