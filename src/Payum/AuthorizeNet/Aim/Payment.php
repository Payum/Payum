<?php
namespace Payum\AuthorizeNet\Aim;

use Payum\Payment as BasePayment;
use Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet\AuthorizeNetAIM;
use Payum\AuthorizeNet\Aim\Action\CaptureAction;
use Payum\AuthorizeNet\Aim\Action\StatusAction;

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

    /**
     * @param Api $api
     *
     * @return static
     */
    public static function create(AuthorizeNetAIM $api)
    {
        $payment = new static($api);

        $payment->addAction(new CaptureAction());
        $payment->addAction(new StatusAction());

        return $payment;
    }
}