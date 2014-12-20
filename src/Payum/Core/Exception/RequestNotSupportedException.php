<?php
namespace Payum\Core\Exception;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Debug\Humanify;

class RequestNotSupportedException extends InvalidArgumentException
{
    /**
     * @param \Payum\Core\Action\ActionInterface $action
     * @param mixed                              $request
     *
     * @throws RequestNotSupportedException
     */
    public static function assertSupports(ActionInterface $action, $request)
    {
        if (false == $action->supports($request)) {
            throw static::createActionNotSupported($action, $request);
        }
    }

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
     * @param \Payum\Core\Action\ActionInterface $action
     * @param mixed                              $request
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
