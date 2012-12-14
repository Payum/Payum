<?php
namespace Payum\Bundle\PayumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Controller extends BaseController
{
    /**
     * Forwards the request to another controller.
     *
     * @param string $controller The controller name (a string like BlogBundle:Post:index)
     * @param array  $path       An array of path parameters
     * @param array  $query      An array of query parameters
     *
     * @return \Symfony\Component\HttpFoundation\Response A Response instance
     */
    public function handle($controller, array $path = array(), array $query = array())
    {
        /** @var $httpKernel \Symfony\Bundle\FrameworkBundle\HttpKernel */
        $httpKernel = $this->container->get('http_kernel');

        $path['_controller'] = $controller;
        $subRequest = $this->container->get('request')->duplicate($query, null, $path);

        return $httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST, $catch = false);
    }
    
    /**
     * @return \Payum\Bundle\PayumBundle\Context\ContextRegistry
     */
    protected function getPayum()
    {
        return $this->get('payum');
    }
}
