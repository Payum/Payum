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

        $this->scheduleForUpdateIfSupported($context);
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
        $this->scheduleForUpdateIfSupported($context);

        if (false == $context->getPrevious()) {
            foreach ($context->getValue('payum.storage_extension.model_scheduled_for_update', []) as $model) {
                if ($this->storage->support($model)) {
                    $this->storage->update($model);
                }
            }
        }
    }

    /**
     * @param Context $context
     */
    protected function scheduleForUpdateIfSupported(Context $context)
    {
        $request = $context->getRequest();

        if (false == $request instanceof ModelAggregateInterface) {
            return;
        }

        $model = $request->getModel();
        if ($this->storage->support($model)) {
            $modelHash = spl_object_hash($model);
            $firstContext = $context->getPrevious() ? current($context->getPrevious()) : $context;
            $scheduledForUpdateModels = $firstContext->getValue('payum.storage_extension.model_scheduled_for_update', []);

            if (array_key_exists($modelHash, $scheduledForUpdateModels)) {
                return;
            }

            $scheduledForUpdateModels[$modelHash] = $model;

            $firstContext->setValue('payum.storage_extension.model_scheduled_for_update', $scheduledForUpdateModels);
        }
    }
}
