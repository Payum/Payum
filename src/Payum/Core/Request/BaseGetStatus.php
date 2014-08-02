<?php
namespace Payum\Core\Request;

abstract class BaseGetStatus extends BaseModelAware implements GetStatusInterface
{
    /**
     * @var mixed
     */
    protected $model;

    /**
     * @var int
     */
    protected $status;

    /**
     * {@inheritdoc}
     */
    public function __construct($model)
    {
        parent::__construct($model);

        $this->markUnknown();
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->status;
    }
}