<?php
namespace Payum\Model;

use Payum\Exception\InvalidArgumentException;

/**
 * @deprecated since 0.6 will be removed in 0.7
 */
class TokenizedDetails extends Token
{
    /**
     * {@inheritDoc}
     *
     * @param Identificator $details
     *
     * @throws InvalidArgumentException if $details is not instance of Identificator
     *
     * @return void
     */
    public function setDetails($details)
    {
        if (false == $details instanceof Identificator) {
            throw new InvalidArgumentException('Details must be instance of `Identificator`.');
        }

        parent::setDetails($details);
    }

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