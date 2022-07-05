<?php

namespace Payum\Core\Model;

interface ModelAwareInterface
{
    /**
     * @param mixed $model
     */
    public function setModel($model);
}
