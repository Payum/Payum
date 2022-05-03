<?php
namespace Payum\Core\Request;

use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Model\ModelAwareInterface;
use Payum\Core\Security\TokenAggregateInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\IdentityInterface;

abstract class Generic implements ModelAwareInterface, ModelAggregateInterface, TokenAggregateInterface
{
    protected mixed $model;

    protected mixed $firstModel;

    protected TokenInterface $token;

    public function __construct(mixed $model)
    {
        $this->setModel($model);

        if ($model instanceof TokenInterface) {
            $this->token = $model;
        }
    }

    public function getModel(): mixed
    {
        return $this->model;
    }

    public function setModel(mixed $model): void
    {
        if (is_array($model)) {
            $model = new \ArrayObject($model);
        }

        $this->model = $model;

        $this->setFirstModel($model);
    }

    /**
     * {@inheritDoc}
     */
    public function getToken(): ?TokenInterface
    {
        return $this->token;
    }

    public function getFirstModel(): mixed
    {
        return $this->firstModel;
    }

    protected function setFirstModel(mixed $model): void
    {
        if ($this->firstModel) {
            return;
        }
        if ($model instanceof TokenInterface) {
            return;
        }
        if ($model instanceof IdentityInterface) {
            return;
        }

        $this->firstModel = $model;
    }
}
