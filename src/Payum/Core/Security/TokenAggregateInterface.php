<?php
namespace Payum\Core\Security;

interface TokenAggregateInterface
{
    /**
     * @return TokenInterface|null
     */
    public function getToken();
}
