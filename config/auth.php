<?php

use App\Models\Accountant;
use App\Models\Admin;
use App\Models\PropertyManager;
use App\Models\Sale;
use App\Models\Tenant;
use App\Models\User;

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards (Multi-guard Multi-actor)
    |--------------------------------------------------------------------------
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'admin' => [
            'driver' => 'session',
            'provider' => 'admins',
        ],

        'sales' => [
            'driver' => 'session',
            'provider' => 'sales',
        ],

        'property_manager' => [
            'driver' => 'session',
            'provider' => 'property_managers',
        ],

        'pm' => [
            'driver' => 'session',
            'provider' => 'property_managers',
        ],

        'accountant' => [
            'driver' => 'session',
            'provider' => 'accountants',
        ],

        'tenant' => [
            'driver' => 'session',
            'provider' => 'tenants',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', User::class),
        ],

        'admins' => [
            'driver' => 'eloquent',
            'model' => Admin::class,
        ],

        'sales' => [
            'driver' => 'eloquent',
            'model' => Sale::class,
        ],

        'property_managers' => [
            'driver' => 'eloquent',
            'model' => PropertyManager::class,
        ],

        'accountants' => [
            'driver' => 'eloquent',
            'model' => Accountant::class,
        ],

        'tenants' => [
            'driver' => 'eloquent',
            'model' => Tenant::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
