<?php
namespace Payum\Request;

abstract class BaseStatusRequest extends BaseModelInteractiveRequest implements StatusRequestInterface
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