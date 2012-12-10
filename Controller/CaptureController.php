<?php
namespace Payum\PaymentBundle\Controller;

use Payum\Domain\ModelInterface;
use Payum\Exception\LogicException;
use Payum\Request\RedirectUrlInteractiveRequest;
use Payum\Request\InteractiveRequestInterface;
use Payum\Request\CaptureRequest;
use Payum\Request\StatusRequestInterface;
use Payum\PaymentBundle\Context\ContextInterface;
use Payum\PaymentBundle\Request\ResponseInteractiveRequest;

class CaptureController extends Controller
{
    public function doAction($contextName, $modelId)
    {
        if (false == $this->getPayum()->hasContext($contextName)) {
            throw $this->createNotFoundException(sprintf('Payment context %s not found', $contextName));
        }
        
        $context = $this->getPayum()->getContext($contextName);
        
        if (false == $model = $context->getStorage()->findModelById($modelId)) {
            throw $this->createNotFoundException(sprintf('Request with id %s not found', $modelId));
        }
        
        if ($interactiveRequest = $context->getPayment()->execute(new CaptureRequest($model))) {
            $context->getStorage()->updateModel($model);
            
            return $this->handle($context->getInteractiveController(), array(
                'context' => $context,
                'interactiveRequest' => $interactiveRequest
            ));
        }

        $statusRequest = $context->createStatusRequest($model);
        if ($interactiveRequest = $context->getPayment()->execute($statusRequest)) {
            throw new LogicException('Unsupported interactive request.', null, $interactiveRequest);
        }

        $response = $this->handle($context->getStatusController(), array(
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

    public function statusAction(ContextInterface $context, StatusRequestInterface $statusRequest)
    {
        return $this->render(
            'PayumPaymentBundle:Capture:status.html.'.$this->container->getParameter('payum_payment.template.engine'), 
            array('status' => $statusRequest)
        );
    }

    /**
     * @return \Payum\PaymentBundle\Context\ContextRegistry
     */
    protected function getPayum()
    {
        return $this->get('payum_payment');
    }
}