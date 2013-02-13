<?php
namespace Payum\Request;

class SyncRequest
{
    /**
     * @var object
     */
    protected $model;

    /**
     * @param object $model
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * @return object
     */
    public function getModel()
    {
        return $this->model;
    }
}