<?php
namespace Payum\Core\Reply;

use Payum\Core\Exception\LogicException;
use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Model\ModelAwareInterface;

abstract class BaseModelAware extends LogicException implements ReplyInterface, ModelAwareInterface, ModelAggregateInterface
{
    protected mixed $model;

    public function __construct(mixed $model)
    {
        $this->setModel($model);
    }

    public function getModel(): mixed
    {
        return $this->model;
    }

    public function setModel($model): void
    {
        if (is_array($model)) {
            $model = new \ArrayObject($model);
        }

        $this->model = $model;
    }
}
