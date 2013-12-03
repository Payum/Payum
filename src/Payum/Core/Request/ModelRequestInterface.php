<?php
namespace Payum\Core\Request;

interface ModelRequestInterface 
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