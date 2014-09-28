<?php
namespace Payum\Core\Security;

interface TokenAggregateInterface
{
    /**
     * @return TokenInterface|null
     */
    function getToken();
}