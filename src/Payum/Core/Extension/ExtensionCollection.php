<?php
namespace Payum\Core\Extension;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Reply\ReplyInterface;

class ExtensionCollection implements ExtensionInterface
{
    /**
     * @var ExtensionInterface[]
     */
    protected $extensions = array();

    /**
     * @param ExtensionInterface $extension
     * @param bool               $forcePrepend
     *
     * @return void
     */
    public function addExtension(ExtensionInterface $extension, $forcePrepend = false)
    {
        $forcePrepend ?
            array_unshift($this->extensions, $extension) :
            array_push($this->extensions, $extension)
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function onPreExecute($request)
    {
        foreach ($this->extensions as $extension) {
            $extension->onPreExecute($request);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function onExecute($request, ActionInterface $action)
    {
        foreach ($this->extensions as $extension) {
            $extension->onExecute($request, $action);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function onPostExecute($request, ActionInterface $action)
    {
        foreach ($this->extensions as $extension) {
            $extension->onPostExecute($request, $action);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function onReply(ReplyInterface $reply, $request, ActionInterface $action)
    {
        $inputReply = $reply;
        foreach ($this->extensions as $extension) {
            if (null !== $newReply = $extension->onReply($reply, $request, $action)) {
                $reply = $newReply;
            }
        }

        return $inputReply !== $reply ? $reply : null;
    }

    /**
     * {@inheritDoc}
     */
    public function onException(\Exception $exception, $request, ActionInterface $action = null)
    {
        foreach ($this->extensions as $extension) {
            $extension->onException($exception, $request, $action);
        }
    }
}
