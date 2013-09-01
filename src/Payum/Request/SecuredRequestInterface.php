<?php
namespace Payum\Request;

interface SecuredRequestInterface
{
    /**
     * @return \Payum\Security\TokenInterface
     */
    function getToken();
}