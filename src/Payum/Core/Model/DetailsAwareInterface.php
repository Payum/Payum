<?php
namespace Payum\Core\Model;

interface DetailsAwareInterface
{
    /**
     * @param object $details
     *
     * @return void
     */
    public function setDetails($details);
}
