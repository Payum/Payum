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

        if (false == is_object($model)) {
            $modelId = $model;
            if (false == $model = $context->getStorage()->findModelById($modelId)) {
                throw $this->createNotFoundException(sprintf('Cannot find model with id %s', $modelId));
            }
            
            unset($modelId);
        }

        if ($interactiveRequest = $context->getPayment()->execute(new CaptureRequest($model))) {
            $context->getStorage()->updateModel($model);
            
            return $this->handle($context->getCaptureInteractiveController(), array(
                'context' => $context,
                'interactiveRequest' => $interactiveRequest
            ));
        }

        $statusRequest = $context->createStatusRequest($model);
        if ($interactiveRequest = $context->getPayment()->execute($statusRequest)) {
            throw new LogicException('Unsupported interactive request.', null, $interactiveRequest);
        }

        $response = $this->handle($context->getCaptureFinishedController(), array(
            'context' => $context,
            'statusRequest' => $statusRequest
        ));

        $context->getStorage()->updateModel($model);
        
        return $response;
    }
    
    public function interactiveAction(ContextInterface $context, InteractiveRequestInterface $interactiveRequest)
    {
        if ($interactiveRequest instanceof RedirectUrlInteractiveRequest) {
            return $this->redirect($interactiveRequest->getUrl());
        }
        if ($interactiveRequest instanceof ResponseInteractiveRequest) {
            return $interactiveRequest->getResponse();
        }

        throw new LogicException('Unsupported interactive request.', null, $interactiveRequest);
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
