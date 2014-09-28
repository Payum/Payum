<?php
namespace Payum\Core\Request;

use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Model\ModelAwareInterface;
use Payum\Core\Security\TokenAggregateInterface;
use Payum\Core\Security\TokenInterface;

abstract class Generic implements ModelAwareInterface, ModelAggregateInterface, TokenAggregateInterface
{
    /**
     * @var mixed
     */
    protected $model;

    /**
     * @var TokenInterface
     */
    protected $token;

    /**
     * @param mixed $model
     */
    public function __construct($model)
    {
        $this->setModel($model);

        if ($model instanceof TokenInterface) {
            $this->token = $model;
        }
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

    /**
     * {@inheritDoc}
     */
    public function getToken()
    {
        return $this->token;
    }
}