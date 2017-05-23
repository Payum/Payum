<?php
/**
 * Created by PhpStorm.
 * User: carlos
 * Date: 1/11/14
 * Time: 13:35
 */


namespace Payum\Redsys;

use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Action\GetHttpRequestAction;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Payum\Core\Payment;
use Payum\Redsys\Action\CaptureAction;
use Payum\Redsys\Action\FillOrderDetailsAction;
use Payum\Redsys\Action\StatusAction;

abstract class PaymentFactory
{
    /**
     * @return \Payum\Core\Payment
     */
    public static function create( Api $api )
    {
        $payment = new Payment;

        $payment->addApi( $api );
        $payment->addExtension( new EndlessCycleDetectorExtension );
        $payment->addAction( new CaptureAction );
        $payment->addAction( new FillOrderDetailsAction );
        $payment->addAction( new StatusAction );
        $payment->addAction( new ExecuteSameRequestWithModelDetailsAction );
        $payment->addAction( new GetHttpRequestAction );

        return $payment;
    }
}
