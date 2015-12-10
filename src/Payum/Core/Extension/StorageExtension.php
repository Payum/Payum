<?php
namespace Payum\Core\Extension;

use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Storage\IdentityInterface;
use Payum\Core\Storage\StorageInterface;

class StorageExtension implements ExtensionInterface
{
    /**
     * @var \Payum\Core\Storage\StorageInterface
     */
    protected $storage;

    /**
     * @var object[]
     */
    protected $scheduledForUpdateModels = array();

    /**
     * @param \Payum\Core\Storage\StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * {@inheritDoc}
     */
    public function onPreExecute(Context $context)
    {
        $request = $context->getRequest();

        if (false == $request instanceof ModelAggregateInterface) {
            return;
        }

        if ($request->getModel() instanceof IdentityInterface) {
            /** @var IdentityInterface $identity */
            $identity = $request->getModel();
            if (false == $model = $this->storage->find($identity)) {
                return;
            }

            $request->setModel($model);
        }

        $this->scheduleForUpdateIfSupported($request->getModel());
    }

    /**
     * {@inheritDoc}
     */
    public function onExecute(Context $context)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onPostExecute(Context $context)
    {
        $request = $context->getRequest();

        if ($request instanceof ModelAggregateInterface) {
            $this->scheduleForUpdateIfSupported($request->getModel());
        }

        if (false == $context->getPrevious()) {
            foreach ($this->scheduledForUpdateModels as $modelHash => $model) {
                $this->storage->update($model);
                unset($this->scheduledForUpdateModels[$modelHash]);
            }
        }
    }

    /**
     * @param mixed $model
     */
    protected function scheduleForUpdateIfSupported($model)
    {
        if ($this->storage->support($model)) {
            $modelHash = spl_object_hash($model);
            if (array_key_exists($modelHash, $this->scheduledForUpdateModels)) {
                return;
            }

            $this->scheduledForUpdateModels[$modelHash] = $model;
        }
    }
}
