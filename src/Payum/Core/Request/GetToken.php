<?php
namespace Payum\Core\Request;

use Payum\Core\Security\TokenInterface;

class GetToken
{
    /**
     * @var string
     */
    private $hash;

    /**
     * @var TokenInterface
     */
    private $token;

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

    /**
     * @param TokenInterface $token
     */
    public function setToken(TokenInterface $token)
    {
        $this->token = $token;
    }
}
