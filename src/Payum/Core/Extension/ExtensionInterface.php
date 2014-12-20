<?php
namespace Payum\Core\Extension;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Reply\ReplyInterface;

interface ExtensionInterface
{
    /**
     * @param mixed $request
     */
    public function onPreExecute($request);

    /**
     * @param mixed                              $request
     * @param \Payum\Core\Action\ActionInterface $action
     */
    public function onExecute($request, ActionInterface $action);

    /**
     * @param mixed                              $request
     * @param \Payum\Core\Action\ActionInterface $action
     */
    public function onPostExecute($request, ActionInterface $action);

    /**
     * @param \Payum\Core\Reply\ReplyInterface   $reply
     * @param mixed                              $request
     * @param \Payum\Core\Action\ActionInterface $action
     *
     * @return null|\Payum\Core\Reply\ReplyInterface an extension able to change reply to something else.
     */
    public function onReply(ReplyInterface $reply, $request, ActionInterface $action);

    /**
     * @param \Exception                              $exception
     * @param mixed                                   $request
     * @param \Payum\Core\Action\ActionInterface|null $action
     */
    public function onException(\Exception $exception, $request, ActionInterface $action = null);
}
