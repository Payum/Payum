<?php
namespace Payum\Core\Request;

interface ModelAwareInterface
{
    /**
     * @param mixed $model
     * 
     * @return void
     */
    function setModel($model);

    /**
     * @return mixed
     */
    function getModel();
}