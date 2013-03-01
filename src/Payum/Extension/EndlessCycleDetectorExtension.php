<?php
namespace Payum\Extension;

use Payum\Action\ActionInterface;
use Payum\Exception\LogicException;
use Payum\Request\InteractiveRequestInterface;

class EndlessCycleDetectorExtension implements ExtensionInterface 
{
    /**
     * @var mixed
     */
    protected $firstRequest;

    /**
     * @var int
     */
    protected $cyclesCounter;

    /**
     * @var int
     */
    protected $limit;

    /**
     * @param int $limit
     */
    public function __construct($limit = 100) 
    {
        $this->limit = $limit;
    }
    
    /**
     * {@inheritdoc}
     */
    public function onPreExecute($request)
    {
        if (null === $this->firstRequest) {
            $this->firstRequest = $request;
            $this->cyclesCounter = 0;
        }
        
        if ($this->cyclesCounter == $this->limit) {
            $cycles = $this->cyclesCounter;
            $this->firstRequest = null;
            $this->cyclesCounter = 0;
            
            throw new LogicException(sprintf(
                'Possible endless cycle detected. ::onPreExecute was called %d times before reach the limit.',
                $cycles
            ));
        }
        
        $this->cyclesCounter++;
    }

    /**
     * {@inheritdoc}
     */
    public function onExecute($request, ActionInterface $action)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function onPostExecute($request, ActionInterface $action)
    {
        if ($request === $this->firstRequest) {
            $this->firstRequest = null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onInteractiveRequest(InteractiveRequestInterface $interactiveRequest, $request, ActionInterface $action)
    {
        if ($request === $this->firstRequest) {
            $this->firstRequest = null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onException(\Exception $exception, $request, ActionInterface $action = null)
    {
        if ($request === $this->firstRequest) {
            $this->firstRequest = null;
        }
    }
}