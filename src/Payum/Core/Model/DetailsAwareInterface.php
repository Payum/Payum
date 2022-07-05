<?php

namespace Payum\Core\Model;

interface DetailsAwareInterface
{
    /**
     * @param object $details
     */
    public function setDetails($details);
}
