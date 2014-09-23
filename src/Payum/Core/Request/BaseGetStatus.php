<?php
namespace Payum\Core\Request;

abstract class BaseGetStatus extends Generic implements GetStatusInterface
{
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
    public function getValue()
    {
        return $this->status;
    }
}