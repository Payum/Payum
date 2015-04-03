<?php
namespace Payum\Core\Request;

use Payum\Core\Security\TokenInterface;

class Convert
{
    /**
     * @var mixed
     */
    protected $from;

    /**
     * @var mixed
     */
    protected $to;

    /**
     * @var TokenInterface|null
     */
    protected $token;

    /**
     * @param mixed $from
     * @param TokenInterface $token
     */
    public function __construct($from, TokenInterface $token = null)
    {
        $this->from = $from;
        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return null|TokenInterface
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return array
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param array $to
     */
    public function setTo(array $to)
    {
        $this->to = $to;
    }
}
