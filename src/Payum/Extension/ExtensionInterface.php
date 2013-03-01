<?php
namespace Payum\Extension;

use Payum\Action\ActionInterface;
use Payum\Request\InteractiveRequestInterface;


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
     * @param \Payum\Action\ActionInterface $action
     *
     * @return void
     */
    function onExecute($request, ActionInterface $action);

    /**
     * @param mixed $request
     * @param \Payum\Action\ActionInterface $action
     *
     * @return void
     */
    function onPostExecute($request, ActionInterface $action);

    /**
     * @param \Payum\Request\InteractiveRequestInterface $interactiveRequest
     * @param mixed $request
     * @param \Payum\Action\ActionInterface $action
     *
     * @return null|InteractiveRequestInterface an extension able to change interactive request to something else.
     */
    function onInteractiveRequest(InteractiveRequestInterface $interactiveRequest, $request, ActionInterface $action);

    /**
     * @param \Exception $exception
     * @param mixed $request
     * @param \Payum\Action\ActionInterface $action
     *
     * @return void
     */
    function onException(\Exception $exception, $request, ActionInterface $action = null);
}