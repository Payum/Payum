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
     * @param \Payum\Storage\StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * {@inheritdoc}
     */
    public function onPreExecute($request)
    {
        if (false == $request instanceof ModelRequestInterface) {
            return;
        }
        
        if (false == $request->getModel() instanceof Identificator) {
            return;
        }
        
        /** @var Identificator $identificator */
        $identificator = $request->getModel();
        
        if (false == $this->storage->supportModel($identificator->getClass())) {
            return;
        }
        
        if (false == $model = $this->storage->findModelById($identificator->getId())) {
            throw new LogicException('Cannot find model by identifier: '.$identificator);
        }

        $request->setModel($model);
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
        $this->tryUpdateModel($request);
    }

    /**
     * {@inheritdoc}
     */
    public function onInteractiveRequest(InteractiveRequestInterface $interactiveRequest, $request, ActionInterface $action)
    {
        $this->tryUpdateModel($request);
    }

    /**
     * {@inheritdoc}
     */
    public function onException(\Exception $exception, $request, ActionInterface $action = null)
    {
        $this->tryUpdateModel($request);
    }

    /**
     * @param mixed $request
     */
    protected function tryUpdateModel($request)
    {
        if ($request instanceof ModelRequestInterface && $this->storage->supportModel($request->getModel())) {
            $this->storage->updateModel($request->getModel());
        }
    }
}