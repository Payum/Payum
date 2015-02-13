<?php
namespace Payum\Core\Extension;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;

class GenericTokenFactoryExtension implements ExtensionInterface
{
    /**
     * @var GenericTokenFactoryInterface
     */
    protected $genericTokenFactory;

    /**
     * @param GenericTokenFactoryInterface $genericTokenFactory
     */
    public function __construct(GenericTokenFactoryInterface $genericTokenFactory)
    {
        $this->genericTokenFactory = $genericTokenFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function onPreExecute($request)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onExecute($request, ActionInterface $action)
    {
        if ($action instanceof GenericTokenFactoryAwareInterface) {
            $action->setGenericTokenFactory($this->genericTokenFactory);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function onException(\Exception $exception, $request, ActionInterface $action = null)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onPostExecute($request, ActionInterface $action)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onReply(ReplyInterface $reply, $request, ActionInterface $action)
    {
    }
}
