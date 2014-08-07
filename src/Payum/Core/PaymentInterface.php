<?php
namespace Payum\Core;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Extension\ExtensionInterface;

interface PaymentInterface
{
    /**
     * @param mixed $api
     * @param bool $forcePrepend
     *
     * @return void
     */
    function addApi($api, $forcePrepend = false);

    /**
     * @param \Payum\Core\Action\ActionInterface $action
     * @param bool $forcePrepend
     *
     * @return void
     */
    function addAction(ActionInterface $action, $forcePrepend = false);

    /**
     * @param \Payum\Core\Extension\ExtensionInterface $extension
     * @param bool $forcePrepend
     *
     * @return void
     */
    function addExtension(ExtensionInterface $extension, $forcePrepend = false);

    /**
     * @param mixed $request
     * @param boolean $catchReply
     * 
     * @throws \Payum\Core\Exception\RequestNotSupportedException if any action supports the request.
     * @throws \Payum\Core\Reply\ReplyInterface if $catchReply parameter set to false.
     * 
     * @return \Payum\Core\Reply\ReplyInterface|null
     */
    function execute($request, $catchReply = false);
}