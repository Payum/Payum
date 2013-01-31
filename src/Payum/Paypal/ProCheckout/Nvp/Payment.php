<?php

namespace Payum\Paypal\ProCheckout\Nvp;

use Payum\Payment as BasePayment;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class Payment extends BasePayment
{

    /**
     * @var Api
     */
    protected $api;

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
