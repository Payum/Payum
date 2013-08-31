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

        if ($this->storage->supportModel($request->getModel())) {
            $modelHash = spl_object_hash($request->getModel());
            if (array_key_exists($modelHash, $this->scheduledForUpdateModels)) {
                return;
            }

            $this->scheduledForUpdateModels[$modelHash] = $request->getModel();
        }
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
        $this->stackLevel--;

        if (0 === $this->stackLevel) {
            $this->updateScheduledModels();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function onPostExecute($request, ActionInterface $action)
    {
        $this->stackLevel--;

        if (0 === $this->stackLevel) {
            $this->updateScheduledModels();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function onInteractiveRequest(InteractiveRequestInterface $interactiveRequest, $request, ActionInterface $action)
    {
        $this->stackLevel--;

        if (0 === $this->stackLevel) {
            $this->updateScheduledModels();
        }
    }

    protected function updateScheduledModels()
    {
        foreach ($this->scheduledForUpdateModels as $modelHash => $model) {
            $this->storage->updateModel($model);
            unset($this->scheduledForUpdateModels[$modelHash]);
        }
    }
}