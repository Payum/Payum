<?php
/**
 * Created by PhpStorm.
 * User: skadabr
 * Date: 9/25/13
 * Time: 4:28 PM
 */
namespace Payum\Paypal\Rest\Model;

use PayPal\Api\Payment as BasePaymentDetails;

class PaymentDetails extends BasePaymentDetails
{
    protected $idStorage;

    protected $state;



    public function getIdStorage()
    {
        return $this->idStorage;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }
}