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
    protected $requestStackLevel = 0;
    
    /**
     * @var array
     */
    protected $trackedModels = array();

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
        $this->requestStackLevel++;
        
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
            $this->trackModel($request);

            return;
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
        $this->updateTrackedModels();
    }

    /**
     * {@inheritDoc}
     */
    public function onPostExecute($request, ActionInterface $action)
    {
        $this->updateTrackedModels();
    }

    /**
     * {@inheritDoc}
     */
    public function onInteractiveRequest(InteractiveRequestInterface $interactiveRequest, $request, ActionInterface $action)
    {
        $this->updateTrackedModels();
    }

    protected function updateTrackedModels()
    {
        $currentRequestStackLevel = $this->requestStackLevel--;
        
        foreach ($this->trackedModels as $modelHash => $trackedModelData) {
            if ($currentRequestStackLevel != $trackedModelData['requestStackLevelModelIntroduced']) {
                continue;
            }
            
            $this->storage->updateModel($trackedModelData['model']);
            unset($this->trackedModels[$modelHash]);
        }
    }

    /**
     * @param \Payum\Request\ModelRequestInterface $request
     */
    protected function trackModel(ModelRequestInterface $request)
    {
        $model = $request->getModel();
        $modelHash = spl_object_hash($model);
        if (array_key_exists($modelHash, $this->trackedModels)) {
            return;
        }
        
        $this->trackedModels[$modelHash] = array(
            'requestStackLevelModelIntroduced' => $this->requestStackLevel,
            'model' => $model,
        );
    }
}