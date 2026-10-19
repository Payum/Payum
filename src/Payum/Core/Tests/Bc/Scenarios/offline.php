<?php

declare(strict_types=1);

use Payum\Core\Tests\Bc\Scenario;

return [
    new Scenario('offline_select', 'offline', [], [
        [
            'op' => 'select',
        ],
    ]),
    new Scenario('offline_status', 'offline', [], [
        [
            'op' => 'status',
            'details' => [],
        ],
        [
            'op' => 'status',
            'details' => [
                'status' => 'pending',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'status' => 'authorized',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'status' => 'captured',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'status' => 'payedout',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'status' => 'refunded',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'status' => 'canceled',
            ],
        ],
        [
            'op' => 'status',
            'details' => [
                'status' => 'something else',
            ],
        ],
    ]),
    new Scenario('offline_capture_then_refund_by_token', 'offline', [], [
        [
            'op' => 'capture',
            'on' => 'token',
        ],
        [
            'op' => 'refund',
            'on' => 'token',
        ],
    ]),
    new Scenario('offline_capture_unpaid', 'offline', [], [
        [
            'op' => 'capture',
            'on' => 'payment',
        ],
    ], details: [
        'paid' => false,
    ]),
    new Scenario('offline_authorize_then_cancel', 'offline', [], [
        [
            'op' => 'authorize',
            'on' => 'payment',
        ],
        [
            'op' => 'cancel',
            'on' => 'payment',
        ],
        [
            'op' => 'sync',
            'on' => 'payment',
        ],
        [
            'op' => 'notify',
            'on' => 'token',
        ],
    ]),
];
