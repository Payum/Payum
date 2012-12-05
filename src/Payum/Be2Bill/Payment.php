<?php
namespace Payum\Be2Bill;

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
}