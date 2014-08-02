<?php
namespace Payum\Core\Request;

use Payum\Core\Security\TokenInterface;

class SecuredNotify extends Notify implements SecuredRequest
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