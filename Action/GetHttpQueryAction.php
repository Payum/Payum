<?php
namespace Payum\Bundle\PayumBundle\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHttpQueryRequest;
use Symfony\Component\DependencyInjection\ContainerAware;

class GetHttpQueryAction extends ContainerAware implements ActionInterface
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request GetHttpQueryRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        if ($this->container->has('request')) {
            foreach ($this->container->get('request')->query->all() as $name => $value) {
                $request[$name] = $value;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof GetHttpQueryRequest;
    }
}