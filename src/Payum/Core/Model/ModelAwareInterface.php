<?php
namespace Payum\Core\Model;

interface ModelAwareInterface
{
    /**
     * @param mixed $model
     *
     * @return void
     */
    public function setModel($model);
}
