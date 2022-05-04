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
    public function onPreExecute(Context $context)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onExecute(Context $context)
    {
        $action = $context->getAction();
        if ($action instanceof GenericTokenFactoryAwareInterface) {
            $action->setGenericTokenFactory($this->genericTokenFactory);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function onPostExecute(Context $context)
    {
        $action = $context->getAction();
        if ($action instanceof GenericTokenFactoryAwareInterface) {
            $action->setGenericTokenFactory(null);
        }
    }
}
