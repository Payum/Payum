<?php
namespace Payum\PaymentBundle\Controller;

use Payum\Request\RedirectUrlInteractiveRequest;
use Payum\Domain\ModelInterface;
use Payum\Exception\LogicException;
use Payum\Request\InteractiveRequestInterface;
use Payum\Request\CaptureRequest;
use Payum\PaymentBundle\Context\ContextInterface;

class PaymentController extends Controller
{
    public function captureAction($contextName, $modelId)
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

        $context->getStorage()->updateModel($model);

        return $this->handle($context->getStatusController(), array(
            'context' => $context,
            'model' => $model
        ));
    }
    
    public function interactiveAction(ContextInterface $context, InteractiveRequestInterface $request)
    {
        if ($request instanceof RedirectUrlInteractiveRequest) {
            return $this->redirect($request->getUrl());
        }
        //todo user input required

        throw new LogicException('Unsupported interactive request.', null, $request);
    }

    public function statusAction(ContextInterface $context, ModelInterface $model)
    {
        $statusRequest = $context->createStatusRequest($model);
        
        $context->getPayment()->execute($statusRequest);

        return $this->render(
            'PayumPaymentBundle:Payment:status.html.'.$this->container->getParameter('payum_payment.template.engine'), 
            array('status' => $statusRequest)
        );
    }

    /**
     * @return \Payum\PaymentBundle\Context\ContextRegistry
     */
    protected function getPayum()
    {
        return $this->get('payum');
    }
}
