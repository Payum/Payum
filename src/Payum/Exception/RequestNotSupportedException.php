<?php
namespace Payum\Exception;

use Payum\Action\ActionInterface;
use Payum\Debug\Humanify;

class RequestNotSupportedException extends InvalidArgumentException
{
    /**
     * @param mixed $request
     *
     * @return RequestNotSupportedException
     */
    public static function create($request)
    {
        return new self(sprintf('Request %s is not supported.', Humanify::request($request)));
    }

    /**
     * @param ActionInterface $action
     * @param mixed $request
     *
     * @return RequestNotSupportedException
     */
    public static function createActionNotSupported(ActionInterface $action, $request)
    {
        return new self(sprintf('Action %s is not supported the request %s.', 
            Humanify::value($action),
            Humanify::request($request)
        ));
    }
}