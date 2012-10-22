<?php
namespace Payum\Exception;

use Payum\Action\ActionInterface;

class RequestNotSupportedException extends InvalidArgumentException
{
    public static function create($request)
    {
        return new self(sprintf('Request %s is not supported.', 
            is_object($request) ? get_class($request) : gettype($request)
        ));
    }
    
    public static function createActionNotSupported(ActionInterface $action, $request)
    {
        return new self(sprintf('Action %s is not supported the request %s.', 
            get_class($action),
            is_object($request) ? get_class($request) : gettype($request)
        ));
    }
}