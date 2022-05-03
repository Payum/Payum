<?php
namespace Payum\Core\Model;

interface DetailsAwareInterface
{
    public function setDetails(object $details): void;
}
