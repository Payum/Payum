<?php
namespace Payum\Request;

use Payum\Security\TokenInterface;

class SecuredCaptureRequest extends CaptureRequest implements SecuredRequestInterface
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