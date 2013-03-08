<?php
namespace Payum\Bundle\PayumBundle\Controller;

use Payum\Exception\LogicException;
use Payum\Request\RedirectUrlInteractiveRequest;
use Payum\Request\InteractiveRequestInterface;
use Payum\Request\CaptureRequest;
use Payum\Request\StatusRequestInterface;
use Payum\Bundle\PayumBundle\Context\ContextInterface;
use Payum\Bundle\PayumBundle\Request\ResponseInteractiveRequest;

class CaptureController extends Controller
{
    public function doAction($contextName, $model)
    {
        if (false == $this->getPayum()->hasContext($contextName)) {
            throw $this->createNotFoundException(sprintf('Payment context %s not found', $contextName));
        }
        $context = $this->getPayum()->getContext($contextName);

        $captureRequest = new CaptureRequest($model);
        $context->getPayment()->execute($captureRequest);
        
        $statusRequest = $context->createStatusRequest($captureRequest->getModel());
        $context->getPayment()->execute($statusRequest);
        
        $response = $this->handle($context->getCaptureFinishedController(), array(
            'context' => $context,
            'statusRequest' => $statusRequest
        ));
        
        return $response;
    }

    public function finishedAction(ContextInterface $context, StatusRequestInterface $statusRequest)
    {
        return $this->render(
            'PayumBundle:Capture:finished.html.'.$this->container->getParameter('payum.template.engine'), 
            array('status' => $statusRequest)
        );
    }

    /**
     * @return \Payum\Bundle\PayumBundle\Context\ContextRegistry
     */
    protected function getPayum()
    {
        return $this->get('payum');
    }
}