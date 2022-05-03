<?php
namespace Payum\Core\Security;

interface TokenAggregateInterface
{
    public function getToken(): ?TokenInterface;
}
