<?php
namespace Payum\Model;

/**
 * @deprecated since 0.6 will be removed in 0.7
 */
class TokenizedDetails extends Token
{
    /**
     * @return string
     */
    public function getToken()
    {
        return $this->getHash();
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->setHash($token);
    }
}