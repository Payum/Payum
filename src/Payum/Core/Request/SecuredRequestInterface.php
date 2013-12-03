<?php
namespace Payum\Core\Request;

interface SecuredRequestInterface
{
    /**
     * @return \Payum\Core\Security\TokenInterface
     */
    function getToken();
}