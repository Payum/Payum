<?php
namespace Payum\Core\Request;

use Payum\Core\Security\TokenInterface;

class Convert
{
    /**
     * @var mixed
     */
    protected $source;

    /**
     * @var mixed
     */
    protected $result;

    /**
     * @var string
     */
    protected $to;

    /**
     * @var TokenInterface
     */
    private $token;

    /**
     * @param mixed $source
     * @param string $to
     * @param TokenInterface $token
     */
    public function __construct($source, $to, TokenInterface $token = null)
    {
        $this->source = $source;
        $this->to = $to;
        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @return TokenInterface
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param mixed $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }
}
