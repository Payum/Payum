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
                'request' => $interactiveRequest
            ));
        }

        $statusRequest = $context->createStatusRequest($model);
        if ($interactiveRequest = $context->getPayment()->execute($statusRequest)) {
            throw new LogicException('Unsupported interactive request.', null, $interactiveRequest);
        }
        
        $context->getStorage()->updateModel($model);

        return $this->handle($context->getStatusController(), array(
            'context' => $context,
            'request' => $statusRequest
        ));
    }
    
    public function interactiveAction(ContextInterface $context, InteractiveRequestInterface $request)
    {
        if ($request instanceof RedirectUrlInteractiveRequest) {
            return $this->redirect($request->getUrl());
        }
        if ($request instanceof ResponseInteractiveRequest) {
            return $request->getResponse();
        }

        throw new LogicException('Unsupported interactive request.', null, $request);
    }

    public function statusAction(ContextInterface $context, StatusRequestInterface $request)
    {
        return $this->render(
            'PayumPaymentBundle:Capture:status.html.'.$this->container->getParameter('payum_payment.template.engine'), 
            array('status' => $request)
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