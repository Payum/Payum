<?php
namespace Payum\Request;

use Payum\Domain\ModelInterface;

class CaptureRequest
{
    protected $model;
    
    public function __construct(ModelInterface $model)
    {
        $this->model = $model;
    }

    /**
     * @return ModelInterface
     */
    public function getModel()
    {
        return $this->model;
    }
}