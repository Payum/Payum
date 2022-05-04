<?php
namespace Payum\Stripe\Request\Api;

use Payum\Core\Request\Generic;

class CreateTokenForCreditCard extends Generic
{
    /**
     * @var array|\ArrayAccess
     */
    protected $token = [];

    /**
     * @return array|\ArrayAccess
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param array|\ArrayAccess $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }
}
