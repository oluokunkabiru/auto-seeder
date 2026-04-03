<?php

/**
 * Auto-Seeder Configuration
 *
 * Publish this file to your Laravel project:
 *   php artisan vendor:publish --tag=auto-seeder-config
 *
 * Then edit config/auto-seeder.php to customise how data is generated.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Default Locale
    |--------------------------------------------------------------------------
    | Faker locale used for generating realistic data.
    | See https://fakerphp.org/locales/ for available locales.
    |
    | Example: 'en_US', 'fr_FR', 'de_DE', 'ar_SA'
    */
    'locale' => env('AUTO_SEEDER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Default Row Count
    |--------------------------------------------------------------------------
    | Number of rows to insert when no count is explicitly passed to seed().
    | AutoSeeder::fromModel(User::class)->seed(); // uses this value
    */
    'default_count' => (int) env('AUTO_SEEDER_DEFAULT_COUNT', 1),

    /*
    |--------------------------------------------------------------------------
    | Column Format Options
    |--------------------------------------------------------------------------
    | Configure per-column data generation. The key is matched against column
    | names (exact or partial). Supported options per column type:
    |
    |   Email columns:
    |     'domain' => 'acme.com'     → generates user@acme.com
    |
    |   Phone/mobile columns:
    |     'country_code' => '+234'   → prepends country code to number
    |
    | Example:
    |   'columns' => [
    |       'email'         => ['domain' => 'company.com'],
    |       'phone'         => ['country_code' => '+234'],
    |       'mobile_number' => ['country_code' => '+44'],
    |   ],
    */
    'columns' => [
        'email' => [
            'domain' => env('AUTO_SEEDER_EMAIL_DOMAIN', null),
        ],
        'phone' => [
            'country_code' => env('AUTO_SEEDER_PHONE_COUNTRY_CODE', null),
        ],
        'mobile' => [
            'country_code' => env('AUTO_SEEDER_PHONE_COUNTRY_CODE', null),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Skip Columns
    |--------------------------------------------------------------------------
    | Columns that should never be seeded, in addition to the built-in list
    | (id, created_at, updated_at, deleted_at, remember_token, email_verified_at).
    | Add any application-specific columns here.
    */
    'skip' => [
        // 'two_factor_secret',
        // 'stripe_id',
    ],

    /*
    |--------------------------------------------------------------------------
    | Type Overrides
    |--------------------------------------------------------------------------
    | Override how specific DB column types are interpreted.
    | Map a DB type string → one of the supported generator hints:
    |   'string', 'integer', 'float', 'boolean', 'date', 'datetime',
    |   'time', 'text', 'json', 'uuid'
    |
    | Example:
    |   'types' => [
    |       'tinytext' => 'string',
    |       'year'     => 'integer',
    |   ],
    */
    'types' => [],

    /*
    |--------------------------------------------------------------------------
    | Web Dashboard
    |--------------------------------------------------------------------------
    | Enable or disable the built-in web dashboard.
    |
    |   URL:  /{route_prefix}          → Model seeder cards
    |   URL:  /{route_prefix}/settings → Settings panel
    */
    'dashboard_enabled' => env('AUTO_SEEDER_DASHBOARD', true),

    /*
    | Route prefix for the dashboard (default: "auto-seeder")
    | Access at: http://your-app.test/auto-seeder
    */
    'route_prefix' => env('AUTO_SEEDER_ROUTE_PREFIX', 'auto-seeder'),

    /*
    | Middleware applied to dashboard routes.
    | Add 'auth' here to restrict access to logged-in users.
    */
    'route_middleware' => ['web'],

    /*
    | Directory to scan for Eloquent models.
    */
    'models_path' => env('AUTO_SEEDER_MODELS_PATH', ''),   // empty = app_path('Models') at runtime

    /*
    | Base namespace for the models directory.
    */
    'models_namespace' => env('AUTO_SEEDER_MODELS_NAMESPACE', 'App\\Models'),

];
