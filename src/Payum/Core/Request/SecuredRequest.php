<?php
namespace Payum\Core\Request;

interface SecuredRequest
{
    /**
     * @return \Payum\Core\Security\TokenInterface
     */
    function getToken();
}