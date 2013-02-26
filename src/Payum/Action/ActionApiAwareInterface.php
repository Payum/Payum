<?php
namespace Payum\Action;

use Payum\Exception\UnsupportedApiException;

interface ActionApiAwareInterface extends ActionInterface
{
    /**
     * @param mixed $api
     * 
     * @throws UnsupportedApiException if the given Api is not supported by an action.
     * 
     * @return void
     */
    function setApi($api);
}