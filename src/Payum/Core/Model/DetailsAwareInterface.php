<?php

namespace Payum\Core\Model;

interface DetailsAwareInterface
{
    public function setDetails(mixed $details): void;
}
