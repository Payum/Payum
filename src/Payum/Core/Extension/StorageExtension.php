<?php
namespace Payum\Core\Extension;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Storage\IdentityInterface;
use Payum\Core\Storage\StorageInterface;

class StorageExtension implements ExtensionInterface
{
    /**
     * @var \Payum\Core\Storage\StorageInterface
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
     * @param \Payum\Core\Storage\StorageInterface $storage
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
    public function onReply(ReplyInterface $reply, $request, ActionInterface $action)
    {
        $this->onPostXXX($request);
    }

    protected function onPostXXX($request)
    {
        $this->stackLevel--;

        if ($request instanceof ModelAggregateInterface) {
            $this->scheduleForUpdateIfSupported($request->getModel());
        }

        if (0 === $this->stackLevel) {
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
