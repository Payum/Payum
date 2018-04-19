<?php
namespace Payum\Core\Exception;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Debug\Humanify;
use Payum\Core\Request\Generic;
use Payum\Core\Storage\IdentityInterface;

class RequestNotSupportedException extends InvalidArgumentException
{
    /**
     * @var mixed
     */
    protected $request;

    /**
     * @var ActionInterface|null
     */
    protected $action;

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return ActionInterface|null
     */
    public function getAction()
    {
        return $this->action;
    }

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
        $exception = new self(sprintf(
            'Request %s is not supported. %s',
            Humanify::request($request),
            implode(" ", static::suggestions($request))
        ));

        $exception->request = $request;

        return $exception;
    }

    /**
     * @param \Payum\Core\Action\ActionInterface $action
     * @param mixed                              $request
     *
     * @return RequestNotSupportedException
     */
    public static function createActionNotSupported(ActionInterface $action, $request)
    {
        $exception = new self(sprintf("Action %s is not supported the request %s. %s",
            Humanify::value($action),
            Humanify::request($request),
            implode(" ", static::suggestions($request))
        ));

        $exception->request = $request;
        $exception->action = $action;

        return $exception;
    }

    /**
     * @param $request
     *
     * @return string[]
     */
    protected static function suggestions($request)
    {
        $suggestions = [];

        if ($request instanceof Generic && $request->getModel() instanceof IdentityInterface) {
            $suggestions[] = sprintf(
                'Make sure the storage extension for "%s" is registered to the gateway.',
                $request->getModel()->getClass()
            );

            $suggestions[] = sprintf(
                'Make sure the storage find method returns an instance by id "%s".',
                $request->getModel()->getId()
            );
        }

        $suggestions[] = 'Make sure the gateway supports the requests and there is an action which supports this request (The method returns true).';
        $suggestions[] = 'There may be a bug, so look for a related issue on the issue tracker.';

        return $suggestions;
    }
}
