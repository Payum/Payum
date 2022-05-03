<?php
namespace Payum\Core\Request;

use Payum\Core\Security\TokenInterface;

class Convert
{
    protected mixed $result;

    public function __construct(protected mixed $source, protected string $to, private ?TokenInterface $token = null)
    {
    }

    public function getSource(): mixed
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

    public function getResult(): mixed
    {
        return $this->result;
    }

    public function setResult(mixed $result): void
    {
        $this->result = $result;
    }
}
