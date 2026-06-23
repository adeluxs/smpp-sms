<?php

return [
    'connections' => [
        'rabbitmq' => [
            'driver' => 'rabbitmq',
            'host' => env('RABBITMQ_HOST', '127.0.0.1'),
            'port' => env('RABBITMQ_PORT', 5672),
            'user' => env('RABBITMQ_USER', 'guest'),
            'password' => env('RABBITMQ_PASSWORD', 'guest'),
            'vhost' => env('RABBITMQ_VHOST', '/'),
        ],
    ],

    'queues' => [
        'smpp.submit' => [
            'connection' => 'rabbitmq',
            'queue' => 'smpp.submit',
            'exchange' => 'smpp.submit.exchange',
        ],

        'smpp.dlr' => [
            'connection' => 'rabbitmq',
            'queue' => 'smpp.dlr',
            'exchange' => 'smpp.dlr.exchange',
        ],

        'smpp.provider' => [
            'connection' => 'rabbitmq',
            'queue' => 'smpp.provider',
            'exchange' => 'smpp.provider.exchange',
        ],
    ],

    'defaults' => [
        'connection' => env('QUEUE_CONNECTION', 'rabbitmq'),
        'retry_after' => 90,
        'block_for' => 0,
    ],
];