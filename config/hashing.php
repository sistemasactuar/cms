<?php

return [
    'default' => 'bcrypt',

    'hashers' => [
        'bcrypt' => [
            'rounds' => env('BCRYPT_ROUNDS', 10),
        ],
    ],
];
