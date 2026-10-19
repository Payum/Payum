<?php

declare(strict_types=1);

use Payum\Core\Tests\Bc\Scenario;

$nvp = [
    'username' => 'bc_user',
    'password' => 'bc_pass',
    'signature' => 'bc_sig',
    'sandbox' => true,
];

$proCheckout = [
    'username' => 'bc_user',
    'password' => 'bc_pass',
    'partner' => 'PayPal',
    'vendor' => 'bc_vendor',
    'tender' => 'C',
    'sandbox' => true,
];

return [
    new Scenario('paypal_pro_checkout_select', 'paypal_pro_checkout', $proCheckout, [
        [
            'op' => 'select',
        ],
    ]),
    new Scenario('paypal_pro_checkout_status', 'paypal_pro_checkout', $proCheckout, [
        [
            'op' => 'status',
            'details' => [],
        ],
        [
            'op' => 'status',
            'details' => [
                'RESULT' => 'x',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'RESULT' => 12,
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'RESULT' => 0,
                'TRXTYPE' => 'S',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'RESULT' => 0,
                'TRXTYPE' => 'C',
                'ORIGID' => 'O1',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'RESULT' => 0,
                'TRXTYPE' => 'A',
            ],
        ],
    ]),
    new Scenario('paypal_pro_checkout_capture_asks_for_a_card', 'paypal_pro_checkout', $proCheckout, [
        [
            'op' => 'capture',
            'on' => 'token',
        ],
    ]),
    new Scenario('paypal_pro_hosted_select', 'paypal_pro_hosted', $nvp, [
        [
            'op' => 'select',
        ],
    ]),
    new Scenario('paypal_pro_hosted_status', 'paypal_pro_hosted', $nvp, [
        [
            'op' => 'status',
            'details' => [],
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
                'PAYMENTSTATUS' => 'Completed',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'PAYMENTSTATUS' => 'Refunded',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'PAYMENTSTATUS' => 'Pending',
                'PENDINGREASON' => 'authorization',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'PAYMENTSTATUS' => 'Pending',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'PAYMENTSTATUS' => 'Failed',
            ],
        ],
    ]),
    new Scenario('paypal_pro_hosted_capture', 'paypal_pro_hosted', $nvp, [
        [
            'op' => 'capture',
            'on' => 'token',
            'http' => [
                [200, 'ACK=Success&EMAILLINK=https%3A%2F%2Fsecurepayments.sandbox.paypal.com%2Fwebapps%2FHostedSoleSolutionApp%2Fwebflow%2Fsparta%2Fhostedsoleflow%3Fhosted_button_id%3DHSS-1&HOSTEDBUTTONID=HSS-1&WEBSITECODE=x', 'METHOD=BMCreateButton'],
            ],
        ],
    ]),
    new Scenario('paypal_masspay_select', 'paypal_masspay', $nvp, [
        [
            'op' => 'select',
        ],
    ]),
    new Scenario('paypal_masspay_status', 'paypal_masspay', $nvp, [
        [
            'op' => 'status',
            'details' => [],
        ],
        [
            'op' => 'status',
            'details' => [
                'ACK' => 'Success',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'ACK' => 'SuccessWithWarning',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'ACK' => 'Failure',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'ACK' => 'Other',
            ],
        ],
    ]),
    new Scenario('paypal_rest_select', 'paypal_rest', [
        'client_id' => 'bc_client',
        'client_secret' => 'bc_secret',
        'config_path' => '',
    ], [
        [
            'op' => 'select',
        ],
    ]),
    new Scenario('paypal_rest_status', 'paypal_rest', [
        'client_id' => 'bc_client',
        'client_secret' => 'bc_secret',
        'config_path' => '',
    ], [
        [
            'op' => 'status',
            'details' => [],
        ],
        [
            'op' => 'status',
            'details' => [
                'state' => 'approved',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'state' => 'created',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'state' => 'cancelled',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'state' => 'other',
            ],
        ],
    ]),
];
