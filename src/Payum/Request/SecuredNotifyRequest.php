<?php
namespace Payum\Request;

use Payum\Security\TokenInterface;

class SecuredNotifyRequest extends NotifyRequest implements SecuredRequestInterface
{
    /**
     * @var TokenInterface
     */
    protected $token;

    /**
     * @param array $notification
     * @param TokenInterface $token
     */
    public function __construct(array $notification, TokenInterface $token)
    {
        parent::__construct($notification, $token);
        
        $this->token = $token;
    }

    /**
     * @return array
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * {@inheritDoc}
     */
    public function getToken()
    {
        return $this->token;
    }
}