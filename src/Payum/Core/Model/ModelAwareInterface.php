<?php
namespace Payum\Core\Model;

interface ModelAwareInterface
{
    public function setModel(mixed $model): void;
}
