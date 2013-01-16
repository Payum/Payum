<?php
namespace Payum\AuthorizeNet\Aim;

use Payum\Payment as BasePayment;
use Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet\AuthorizeNetAIM;

class Payment extends BasePayment
{
    /**
     * @var AuthorizeNetAIM
     */
    protected $api;

    /**
     * @param AuthorizeNetAIM $api
     */
    public function __construct(AuthorizeNetAIM $api)
    {
        $this->api = $api;
    }

    /**
     * @return AuthorizeNetAIM
     */
    public function getApi()
    {
        return $this->api;
    }
}