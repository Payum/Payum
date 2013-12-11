<?php
namespace Payum\Core\Extension;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\InteractiveRequestInterface;

interface ExtensionInterface 
{
    /**
     * @param mixed $request
     *
     * @return void
     */
    function onPreExecute($request);

    /**
     * @param mixed $request
     * @param \Payum\Core\Action\ActionInterface $action
     *
     * @return void
     */
    function onExecute($request, ActionInterface $action);

    /**
     * @param mixed $request
     * @param \Payum\Core\Action\ActionInterface $action
     *
     * @return void
     */
    function onPostExecute($request, ActionInterface $action);

    /**
     * @param \Payum\Core\Request\InteractiveRequestInterface $interactiveRequest
     * @param mixed $request
     * @param \Payum\Core\Action\ActionInterface $action
     *
     * @return null|\Payum\Core\Request\InteractiveRequestInterface an extension able to change interactive request to something else.
     */
    function onInteractiveRequest(InteractiveRequestInterface $interactiveRequest, $request, ActionInterface $action);

    /**
     * @param \Exception $exception
     * @param mixed $request
     * @param \Payum\Core\Action\ActionInterface|null $action
     */
    function onException(\Exception $exception, $request, ActionInterface $action = null);
}