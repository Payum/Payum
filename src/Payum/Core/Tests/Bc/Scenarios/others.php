<?php

declare(strict_types=1);

use Payum\Core\Tests\Bc\Scenario;

$be2bill = [
    'identifier' => 'bc_identifier',
    'password' => 'bc_password',
    'sandbox' => true,
];

$klarnaInvoice = [
    'eid' => '1234',
    'secret' => 'bc_secret',
    'country' => 'SE',
    'language' => 'SV',
    'currency' => 'SEK',
    'sandbox' => true,
];

$klarnaCheckout = [
    'merchant_id' => '1234',
    'secret' => 'bc_secret',
    'sandbox' => true,
];

$authorizeNet = [
    'login_id' => 'bc_login',
    'transaction_key' => 'bc_key',
    'sandbox' => true,
];

$payex = [
    'account_number' => '1234',
    'encryption_key' => 'bc_key',
    'sandbox' => true,
];

$sofort = [
    'config_key' => '12345:123456:abcdef0123456789abcdef0123456789',
];

return [
    new Scenario('be2bill_direct_select', 'be2bill_direct', $be2bill, [
        [
            'op' => 'select',
        ],
    ]),
    new Scenario('be2bill_direct_status', 'be2bill_direct', $be2bill, [
        [
            'op' => 'status',
            'details' => [],
        ],
        [
            'op' => 'status',
            'details' => [
                'EXECCODE' => '0000',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'EXECCODE' => '5004',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'EXECCODE' => '4001',
            ],
        ],
    ]),
    new Scenario('be2bill_direct_capture_asks_for_a_card', 'be2bill_direct', $be2bill, [
        [
            'op' => 'capture',
            'on' => 'token',
        ],
    ]),
    new Scenario('be2bill_offsite_select', 'be2bill_offsite', $be2bill, [
        [
            'op' => 'select',
        ],
    ]),
    new Scenario('be2bill_offsite_capture', 'be2bill_offsite', $be2bill, [
        [
            'op' => 'capture',
            'on' => 'token',
        ],
        [
            'op' => 'capture',
            'on' => 'token',
            'get' => [
                'EXECCODE' => '0000',
                'TRANSACTIONID' => 'T1',
            ],
        ],
    ]),
    new Scenario('authorize_net_aim_select', 'authorize_net_aim', $authorizeNet, [
        [
            'op' => 'select',
        ],
    ]),
    new Scenario('authorize_net_aim_status', 'authorize_net_aim', $authorizeNet, [
        [
            'op' => 'status',
            'details' => [],
        ],
        [
            'op' => 'status',
            'details' => [
                'response_code' => 1,
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'response_code' => 2,
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'response_code' => 3,
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'response_code' => 4,
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'response_code' => 9,
            ],
        ],
    ]),
    new Scenario('authorize_net_aim_capture_asks_for_a_card', 'authorize_net_aim', $authorizeNet, [
        [
            'op' => 'capture',
            'on' => 'token',
        ],
    ]),
    new Scenario('klarna_checkout_select', 'klarna_checkout', $klarnaCheckout, [
        [
            'op' => 'select',
        ],
    ]),
    new Scenario('klarna_checkout_status', 'klarna_checkout', $klarnaCheckout, [
        [
            'op' => 'status',
            'details' => [],
        ],
        [
            'op' => 'status',
            'details' => [
                'error_code' => 'E1',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'invoice_number' => 'I1',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'reservation' => 'R1',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'status' => 'checkout_incomplete',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'status' => 'checkout_complete',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'status' => 'created',
            ],
        ],
    ]),
    new Scenario('klarna_invoice_select', 'klarna_invoice', $klarnaInvoice, [
        [
            'op' => 'select',
        ],
    ]),
    new Scenario('klarna_invoice_status', 'klarna_invoice', $klarnaInvoice, [
        [
            'op' => 'status',
            'details' => [],
        ],
        [
            'op' => 'status',
            'details' => [
                'error_code' => 'E1',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'canceled' => true,
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'refund_invoice_number' => 'R1',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'invoice_number' => 'I1',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'status' => 1,
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'status' => 2,
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'status' => 3,
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'status' => 99,
            ],
        ],
    ]),
    new Scenario('payex_select', 'payex', $payex, [
        [
            'op' => 'select',
        ],
    ]),
    new Scenario('payex_status', 'payex', $payex, [
        [
            'op' => 'status',
            'details' => [],
        ],
        [
            'op' => 'status',
            'details' => [
                'orderId' => 'O1',
                'transactionStatus' => '0',
                'errorCode' => 'OK',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'orderId' => 'O1',
                'transactionStatus' => '3',
                'errorCode' => 'OK',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'orderId' => 'O1',
                'errorCode' => 'Failed',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'agreementRef' => 'A1',
                'agreementStatus' => 1,
            ],
        ],
    ]),
    new Scenario('sofort_select', 'sofort', $sofort, [
        [
            'op' => 'select',
        ],
    ]),
    new Scenario('sofort_status', 'sofort', $sofort, [
        [
            'op' => 'status',
            'details' => [],
        ],
        [
            'op' => 'status',
            'details' => [
                'transaction_id' => 'T1',
                'expires' => 1,
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'transaction_id' => 'T1',
                'status' => 'loss',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'transaction_id' => 'T1',
                'status' => 'pending',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'transaction_id' => 'T1',
                'status' => 'received',
                'statusReason' => 'credited',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'transaction_id' => 'T1',
                'status' => 'received',
                'statusReason' => 'partially_credited',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'transaction_id' => 'T1',
                'status' => 'refunded',
                'statusReason' => 'refunded',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'transaction_id' => 'T1',
                'status' => 'untraceable',
            ],
        ],
    ]),
];
