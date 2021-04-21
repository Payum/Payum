<?php
namespace Payum\Core\Reply;

use Payum\Core\Exception\LogicException;
use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Model\ModelAwareInterface;

abstract class BaseModelAware extends LogicException implements ReplyInterface, ModelAwareInterface, ModelAggregateInterface
{
    /**
     * @var mixed
     */
    protected $model;

    /**
     * @param mixed $model
     */
    public function __construct($model)
    {
        $this->setModel($model);
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param mixed $model
     */
    public function setModel($model)
    {
        if (is_array($model)) {
            $model = new \ArrayObject($model);
        }

        $this->model = $model;
    }
}
