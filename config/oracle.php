<?php

return [
  'sum' => [
    'driver' => 'oracle',
    'tns' => env('DB_SUM_TNS', ''),
    'host' => env('DB_SUM_HOST', ''),
    'port' => env('DB_SUM_PORT', '1521'),
    'database' => env('DB_SUM_DATABASE', ''),
    'service_name' => env('DB_SUM_SERVICE_NAME', ''),
    'username' => env('DB_SUM_USERNAME', ''),
    'password' => env('DB_SUM_PASSWORD', ''),
    'charset' => env('DB_SUM_CHARSET', 'AL32UTF8'),
    'prefix' => env('DB_SUM_PREFIX', ''),
    'prefix_schema' => env('DB_SUM_SCHEMA_PREFIX', ''),
    'edition' => env('DB_SUM_EDITION', 'ora$base'),
    'server_version' => env('DB_SUM_SERVER_VERSION', '11g'),
    'load_balance' => env('DB_SUM_LOAD_BALANCE', 'yes'),
    'max_name_len' => env('ORA_MAX_NAME_LEN', 30),
    'dynamic' => [],
    'sessionVars' => [
      'NLS_TIME_FORMAT' => 'HH24:MI:SS',
      'NLS_DATE_FORMAT' => 'YYYY-MM-DD HH24:MI:SS',
      'NLS_TIMESTAMP_FORMAT' => 'YYYY-MM-DD HH24:MI:SS',
      'NLS_TIMESTAMP_TZ_FORMAT' => 'YYYY-MM-DD HH24:MI:SS TZH:TZM',
      'NLS_NUMERIC_CHARACTERS' => '.,',
    ],
  ],
  'rrhh' => [
    'driver' => 'oracle',
    'tns' => env('DB_RRHH_TNS', ''),
    'host' => env('DB_RRHH_HOST', ''),
    'port' => env('DB_RRHH_PORT', '1521'),
    'database' => env('DB_RRHH_DATABASE', ''),
    'service_name' => env('DB_RRHH_SERVICE_NAME', ''),
    'username' => env('DB_RRHH_USERNAME', ''),
    'password' => env('DB_RRHH_PASSWORD', ''),
    'charset' => env('DB_RRHH_CHARSET', 'AL32UTF8'),
    'prefix' => env('DB_RRHH_PREFIX', ''),
    'prefix_schema' => env('DB_RRHH_SCHEMA_PREFIX', ''),
    'edition' => env('DB_RRHH_EDITION', 'ora$base'),
    'server_version' => env('DB_RRHH_SERVER_VERSION', '11g'),
    'load_balance' => env('DB_RRHH_LOAD_BALANCE', 'yes'),
    'max_name_len' => env('ORA_MAX_NAME_LEN', 30),
    'dynamic' => [],
    'sessionVars' => [
      'NLS_TIME_FORMAT' => 'HH24:MI:SS',
      'NLS_DATE_FORMAT' => 'YYYY-MM-DD HH24:MI:SS',
      'NLS_TIMESTAMP_FORMAT' => 'YYYY-MM-DD HH24:MI:SS',
      'NLS_TIMESTAMP_TZ_FORMAT' => 'YYYY-MM-DD HH24:MI:SS TZH:TZM',
      'NLS_NUMERIC_CHARACTERS' => '.,',
    ],
  ],
];
