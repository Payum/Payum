<?php
namespace Payum\Core\Model;

interface DetailsAwareInterface  
{
    /**
     * @param object $details
     * 
     * @return void
     */
    function setDetails($details);
}