<?php
namespace Payum\Paypal\ExpressCheckout\Nvp;

interface ActionInterface
{
    /**
     * @return array
     */
    function toNvp();
}