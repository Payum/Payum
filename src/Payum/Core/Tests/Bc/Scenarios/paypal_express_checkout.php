<?php

declare(strict_types=1);

use Payum\Core\Tests\Bc\Scenario;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoExpressCheckoutPayment;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetExpressCheckoutDetails;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\SetExpressCheckout;

$config = [
    'username' => 'bc_user',
    'password' => 'bc_pass',
    'signature' => 'bc_sig',
    'sandbox' => true,
];

return [
    new Scenario('paypal_express_checkout_select', 'paypal_express_checkout', $config, [
        [
            'op' => 'select',
        ],
    ], probes: [[SetExpressCheckout::class], [GetExpressCheckoutDetails::class], [DoExpressCheckoutPayment::class]]),
    new Scenario('paypal_express_checkout_status', 'paypal_express_checkout', $config, [
        [
            'op' => 'status',
            'details' => [],
        ],
        [
            'op' => 'status',
            'details' => [
                'L_ERRORCODE0' => '10486',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'L_ERRORCODE0' => '10001',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'CANCELLED' => true,
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'CHECKOUTSTATUS' => 'PaymentActionNotInitiated',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'CHECKOUTSTATUS' => 'PaymentActionNotInitiated',
                'PAYERID' => 'P1',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'CHECKOUTSTATUS' => 'PaymentActionInProgress',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'CHECKOUTSTATUS' => 'PaymentActionFailed',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'CHECKOUTSTATUS' => 'PaymentActionCompleted',
                'PAYMENTINFO_0_PAYMENTSTATUS' => 'Completed',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'CHECKOUTSTATUS' => 'PaymentActionCompleted',
                'PAYMENTINFO_0_PAYMENTSTATUS' => 'Pending',
                'PAYMENTINFO_0_PENDINGREASON' => 'authorization',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'CHECKOUTSTATUS' => 'PaymentActionCompleted',
                'PAYMENTINFO_0_PAYMENTSTATUS' => 'Pending',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'CHECKOUTSTATUS' => 'PaymentActionCompleted',
                'PAYMENTINFO_0_PAYMENTSTATUS' => 'Refunded',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'CHECKOUTSTATUS' => 'PaymentActionCompleted',
                'PAYMENTINFO_0_PAYMENTSTATUS' => 'Voided',
                'PAYMENTINFO_0_PENDINGREASON' => 'authorization',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'CHECKOUTSTATUS' => 'PaymentActionCompleted',
                'PAYMENTINFO_0_PAYMENTSTATUS' => 'Failed',
            ],
        ],
    ]),
    new Scenario('paypal_express_checkout_capture', 'paypal_express_checkout', $config, [
        [
            'op' => 'capture',
            'on' => 'token',
            'http' => [
                [200, 'ACK=Success&TOKEN=EC-BC1', 'METHOD=SetExpressCheckout'],
                [200, 'ACK=Success&TOKEN=EC-BC1&CHECKOUTSTATUS=PaymentActionNotInitiated', 'METHOD=GetExpressCheckoutDetails'],
            ],
        ],
        [
            'op' => 'capture',
            'on' => 'token',
            'get' => [
                'token' => 'EC-BC1',
                'PayerID' => 'PAYER1',
            ],
            'http' => [
                [200, 'ACK=Success&TOKEN=EC-BC1&PAYERID=PAYER1&CHECKOUTSTATUS=PaymentActionNotInitiated&PAYMENTREQUEST_0_AMT=12.34', 'METHOD=GetExpressCheckoutDetails'],
                [200, 'ACK=Success&TOKEN=EC-BC1&PAYMENTINFO_0_TRANSACTIONID=TX1&PAYMENTINFO_0_PAYMENTSTATUS=Completed', 'METHOD=DoExpressCheckoutPayment'],
                [200, 'ACK=Success&TOKEN=EC-BC1&PAYERID=PAYER1&CHECKOUTSTATUS=PaymentActionCompleted&PAYMENTREQUEST_0_AMT=12.34&PAYMENTREQUEST_0_TRANSACTIONID=TX1', 'METHOD=GetExpressCheckoutDetails'],
                [200, 'ACK=Success&TRANSACTIONID=TX1&PAYMENTSTATUS=Completed', 'METHOD=GetTransactionDetails'],
            ],
        ],
    ]),
    new Scenario('paypal_express_checkout_cancelled_by_buyer', 'paypal_express_checkout', $config, [
        [
            'op' => 'capture',
            'on' => 'token',
            'get' => [
                'cancelled' => '1',
            ],
        ],
    ]),
];
