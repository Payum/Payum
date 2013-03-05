<?php
namespace Payum\Extension;

use Payum\Action\ActionInterface;
use Payum\Request\InteractiveRequestInterface;
use Payum\Request\ModelRequestInterface;
use Payum\Storage\StorageInterface;

class StorageExtension implements ExtensionInterface 
{
    /**
     * @var \Payum\Storage\StorageInterface
     */
    protected $storage;

    /**
     * @var mixed
     */
    protected $firstRequest;

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
        if ($this->firstRequest) {
            return;
        }

        $this->firstRequest = $request;
        
        if (false == $request instanceof ModelRequestInterface) {
            return;
        }
        if (is_object($request->getModel())) {
            return;
        }
    
        if ($model = $this->storage->findModelById($request->getModel())) {
            $request->setModel($model);
        }
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
        if ($this->firstRequest !== $request) {
            return;
        }

        $this->firstRequest = null;

        if ($request instanceof ModelRequestInterface && $this->storage->supportModel($request->getModel())) {
            $this->storage->updateModel($request->getModel());
        }
    }
}