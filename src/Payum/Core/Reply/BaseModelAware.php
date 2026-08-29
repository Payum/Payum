<?php

namespace Payum\Core\Reply;

use ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Model\ModelAwareInterface;

/**
 * @deprecated since 2.0.0, will be removed in 3.0. A handler returns a Payum\Core\Result\Result
 *             carrying a Payum\Core\Result\NextAction instead of throwing a reply; the model it used to
 *             carry is Payum\Core\Handler\Context::subject().
 */
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
    public function setModel($model): void
    {
        if (is_array($model)) {
            $model = new ArrayObject($model);
        }

        $this->model = $model;
    }
}
