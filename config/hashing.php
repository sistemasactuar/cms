<?php

return [
    'default' => env('HASH_DRIVER', 'bcrypt'),

    'hashers' => [
        'bcrypt' => [
            'rounds' => env('BCRYPT_ROUNDS', 10),
        ],
    ],
];
