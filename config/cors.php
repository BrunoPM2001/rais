<?php

return [

  /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: 00000
    |
    */

  'paths' => ['*'],

  'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],

  'allowed_origins' => ['*'],

  'allowed_origins_patterns' => ['*'],

  'allowed_headers' => ['*'],

  'exposed_headers' => ['*'],

  'max_age' => 0,

  'supports_credentials' => false,

];
