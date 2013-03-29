<?php
namespace Payum\Model;

interface DetailsAwareInterface  
{
    /**
     * @param object $details
     * 
     * @return void
     */
    function setDetails($details);
}