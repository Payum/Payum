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

    private ?TokenInterface $token;

    /**
     * @param mixed $source
     * @param string $to
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

    public function getTo(): string
    {
        return $this->to;
    }

    public function getToken(): ?TokenInterface
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
    public function setResult($result): void
    {
        $this->result = $result;
    }
}
