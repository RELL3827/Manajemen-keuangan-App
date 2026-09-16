<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Hash Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default hash driver that will be used to hash
    | passwords for your application. By default, the bcrypt algorithm is
    | used; however, you're free to modify this option if you wish.
    |
    | Supported: "bcrypt", "argon", "argon2id"
    |
    */

    'driver' => (!empty(env('HASH_DRIVER')) ? env('HASH_DRIVER') : 'bcrypt'),

    /*
    |--------------------------------------------------------------------------
    | Bcrypt Options
    |--------------------------------------------------------------------------
    |
    | Here you may specify the configuration options for the bcrypt algorithm.
    | The rounds option allows you to specify the work factor used to hash
    | the password. You may increase this value if you need more security.
    |
    */

    'bcrypt' => [
        'rounds' => (!empty(env('BCRYPT_ROUNDS')) && (int)env('BCRYPT_ROUNDS') >= 4 && (int)env('BCRYPT_ROUNDS') <= 31) ? (int)env('BCRYPT_ROUNDS') : 10,
        'verify' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Argon Options
    |--------------------------------------------------------------------------
    |
    | Here you may specify the configuration options for the Argon algorithm.
    | The memory option represents the maximum amount of memory in kilobytes
    | that may be used, while the threads option is the number of threads.
    |
    */

    'argon' => [
        'memory' => (!empty(env('ARGON_MEMORY')) ? (int)env('ARGON_MEMORY') : 65536),
        'threads' => (!empty(env('ARGON_THREADS')) ? (int)env('ARGON_THREADS') : 1),
        'time' => (!empty(env('ARGON_TIME')) ? (int)env('ARGON_TIME') : 4),
        'verify' => true,
    ],

];
