<?php
namespace Payum;

use Payum\Action\ActionInterface;
use Payum\Extension\ExtensionInterface;

interface PaymentInterface
{
    /**
     * @param mixed $api
     *
     * @return void
     */
    function addApi($api);

    /**
     * @param ActionInterface $action
     * @param bool $forcePrepend
     *
     * @return void
     */
    function addAction(ActionInterface $action, $forcePrepend = false);

    /**
     * @param \Payum\Extension\ExtensionInterface $extension
     * @param bool $forcePrepend
     *
     * @return void
     */
    function addExtension(ExtensionInterface $extension, $forcePrepend = false);

    /**
     * @param mixed $request
     * @param boolean $catchInteractive
     * 
     * @throws \Payum\Exception\RequestNotSupportedException if any action supports the request.
     * @throws \Payum\Request\InteractiveRequestInterface if $catchInteractive parameter set to false.
     * 
     * @return \Payum\Request\InteractiveRequestInterface|null
     */
    function execute($request, $catchInteractive = false);
}