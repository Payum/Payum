<?php
namespace Payum\Extension;

use Payum\Action\ActionInterface;
use Payum\Exception\LogicException;
use Payum\Request\InteractiveRequestInterface;
use Payum\Request\ModelRequestInterface;
use Payum\Storage\Identificator;
use Payum\Storage\StorageInterface;

class StorageExtension implements ExtensionInterface 
{
    /**
     * @var \Payum\Storage\StorageInterface
     */
    protected $storage;

    /**
     * @var int
     */
    protected $stackLevel = 0;
    
    /**
     * @var object[]
     */
    protected $scheduledForUpdateModels = array();

    /**
     * @param \Payum\Storage\StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * {@inheritDoc}
     */
    public function onPreExecute($request)
    {
        $this->stackLevel++;
        
        if (false == $request instanceof ModelRequestInterface) {
            return;
        }

        if ($request->getModel() instanceof Identificator) {
            /** @var Identificator $identificator */
            $identificator = $request->getModel();
            if (false == $model = $this->storage->findModelByIdentificator($identificator)) {
                return;
            }

            $request->setModel($model);
        }

        $this->scheduleForUpdateIfSupported($request->getModel());
    }

    /**
     * {@inheritDoc}
     */
    public function onExecute($request, ActionInterface $action)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onException(\Exception $exception, $request, ActionInterface $action = null)
    {
        $this->onPostXXX($request);
    }

    /**
     * {@inheritDoc}
     */
    public function onPostExecute($request, ActionInterface $action)
    {
        $this->onPostXXX($request);
    }

    /**
     * {@inheritDoc}
     */
    public function onInteractiveRequest(InteractiveRequestInterface $interactiveRequest, $request, ActionInterface $action)
    {
        $this->onPostXXX($request);
    }

    protected function onPostXXX($request)
    {
        $this->stackLevel--;

        if ($request instanceof ModelRequestInterface) {
            $this->scheduleForUpdateIfSupported($request->getModel());
        }

        if (0 === $this->stackLevel) {
            foreach ($this->scheduledForUpdateModels as $modelHash => $model) {
                $this->storage->updateModel($model);
                unset($this->scheduledForUpdateModels[$modelHash]);
            }
        }
    }

    /**
     * @param mixed $model
     */
    protected function scheduleForUpdateIfSupported($model)
    {
        if ($this->storage->supportModel($model)) {
            $modelHash = spl_object_hash($model);
            if (array_key_exists($modelHash, $this->scheduledForUpdateModels)) {
                return;
            }

            $this->scheduledForUpdateModels[$modelHash] = $model;
        }
    }
}