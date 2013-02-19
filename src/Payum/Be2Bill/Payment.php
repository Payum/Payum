<?php
namespace Payum\Be2Bill;

use Payum\Be2Bill\Action\CaptureAction;
use Payum\Be2Bill\Action\StatusAction;
use Payum\Payment as BasePayment;

class Payment extends BasePayment
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * @param Api $api
     */
    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    /**
     * @return Api
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * @param Api $api
     * 
     * @return static
     */
    public static function create(Api $api)
    {
        $payment = new static($api);

        $payment->addAction(new CaptureAction());
        $payment->addAction(new StatusAction());
        
        return $payment;
    }
}