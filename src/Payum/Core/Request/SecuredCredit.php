<?php
namespace Payum\Core\Request;

use Payum\Core\Security\TokenInterface;

class SecuredCredit extends Credit implements SecuredInterface
{
    /**
     * @var TokenInterface
     */
    protected $token;

    /**
     * @param TokenInterface $token
     */
    public function __construct(TokenInterface $token)
    {
        $this->token = $token;

        $this->setModel($token);
    }

    /**
     * {@inheritDoc}
     */
    public function getToken()
    {
        return $this->token;
    }
}