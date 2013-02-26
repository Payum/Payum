<?php
namespace Payum;

use Payum\Action\ActionApiAwareInterface;
use Payum\Exception\LogicException;
use Payum\Exception\UnsupportedApiException;
use Payum\Request\InteractiveRequestInterface;
use Payum\Action\ActionPaymentAwareInterface;
use Payum\Action\ActionInterface;
use Payum\Exception\RequestNotSupportedException;
use Payum\Exception\CycleRequestsException;

class Payment implements PaymentInterface
{
    /**
     * @var ActionInterface[]
     */
    protected $actions = array();

    /**
     * @var mixed[]
     */
    protected $apis = array();

    /**
     * @var array
     */
    protected $actionsCallsCounters = array();

    /**
     * @var int
     */
    protected $actionsCallLimit = 100;

    /**
     * @var mixed|null
     */
    protected $firstRequest;

    /**
     * {@inheritdoc}
     */
    public function addApi($api)
    {
        $this->apis[] = $api;
    }
    
    /**
     * {@inheritdoc}
     */
    public function addAction(ActionInterface $action)
    {
        if ($action instanceof ActionPaymentAwareInterface) {
            $action->setPayment($this);
        }
        
        if ($action instanceof ActionApiAwareInterface) {
            $apiSet = false;
            foreach ($this->apis as $api) {
                try {
                    $action->setApi($api);
                    $apiSet = true;
                    break;
                } catch (UnsupportedApiException $e) {}
            }
            
            if (false == $apiSet) {
                throw new LogicException(sprintf(
                    'Cannot find right api supported by %s',
                    get_class($action)
                ));
            }
        }

        $this->actions[spl_object_hash($action)] = $action;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($request, $isInteractiveRequestExpected = false)
    {   
        if (false == $action = $this->findActionSupported($request)) {
            throw RequestNotSupportedException::create($request);
        }

        $this->preExecute($action, $request);
        
        try {
            $action->execute($request);
            
            $this->postExecute($action, $request);
        } catch (InteractiveRequestInterface $interactiveRequest) {
            if ($isInteractiveRequestExpected) {
                $this->postExecute($action, $request);
                
                return $interactiveRequest;
            }
            
            throw $interactiveRequest;
        } catch (\Exception $e) {
            if ($request === $this->firstRequest) {
                $this->postExecute($action, $request);
            }
            
            throw $e;
        }
    }

    /**
     * @param Action\ActionInterface $action
     * @param mixed $request
     * 
     * @return mixed
     */
    protected function preExecute(ActionInterface $action, $request)
    {
        if (null === $this->firstRequest) {
            $this->resetActionCallsCounters();
            $this->firstRequest = $request;
        }

        $this->throwIfActionCallsLimitReached($action);
        $this->incrementActionCallsCounter($action);
    }

    /**
     * @param Action\ActionInterface $action
     * @param mixed $request
     *
     * @return mixed
     */
    protected function postExecute(ActionInterface $action, $request)
    {
        if ($this->firstRequest === $request) {
            $this->firstRequest = null;
        }
    }

    /**
     * @return void
     */
    protected function resetActionCallsCounters()
    {
        foreach ($this->actions as $action) {
            $this->actionsCallsCounters[spl_object_hash($action)] = 0;
        }
    }

    /**
     * @param Action\ActionInterface $action
     *
     * @return void
     */
    protected function incrementActionCallsCounter(ActionInterface $action)
    {
        $this->actionsCallsCounters[spl_object_hash($action)]++;
    }

    /**
     * @param Action\ActionInterface $action
     * 
     * @throws Exception\CycleRequestsException
     * 
     * @return void
     */
    protected function throwIfActionCallsLimitReached(ActionInterface $action)
    {
        if ($this->actionsCallsCounters[spl_object_hash($action)] >= $this->actionsCallLimit) {
            throw new CycleRequestsException(sprintf(
                'The action %s is called %d times. Possible requests infinite loop detected.',
                get_class($action),
                $this->actionsCallLimit
            ));
        }
    }

    /**
     * @param mixed $request
     *
     * @return ActionInterface|null
     */
    protected function findActionSupported($request)
    {
        foreach ($this->actions as $action) {
            if ($action->supports($request)) {
                return $action;
            }
        }
    }
}