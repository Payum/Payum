<?php
namespace Payum\Core\Extension;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;

class GenericTokenFactoryExtension implements ExtensionInterface
{
    public function __construct(protected GenericTokenFactoryInterface $genericTokenFactory)
    {}

    /**
     * {@inheritDoc}
     */
    public function onPreExecute(Context $context): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onExecute(Context $context): void
    {
        $action = $context->getAction();
        if ($action instanceof GenericTokenFactoryAwareInterface) {
            $action->setGenericTokenFactory($this->genericTokenFactory);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function onPostExecute(Context $context): void
    {
        $action = $context->getAction();
        if ($action instanceof GenericTokenFactoryAwareInterface) {
            $action->setGenericTokenFactory(null);
        }
    }
}
