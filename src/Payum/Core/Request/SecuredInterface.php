<?php
namespace Payum\Core\Request;

interface SecuredInterface
{
    /**
     * @return \Payum\Core\Security\TokenInterface|null
     */
    function getToken();
}