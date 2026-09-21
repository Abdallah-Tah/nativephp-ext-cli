<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default PHP Version
    |--------------------------------------------------------------------------
    |
    | This value determines the default PHP version to use when building
    | extensions. Supported versions are 8.1, 8.2, 8.3, 8.4, and 8.5.
    |
    */
    'default_php_version' => '8.5',

    /*
    |--------------------------------------------------------------------------
    | Supported PHP Versions
    |--------------------------------------------------------------------------
    */
    'supported_php_versions' => [
        '8.1' => '8.1.31',
        '8.2' => '8.2.29',
        '8.3' => '8.3.15',
        '8.4' => '8.4.13',
        '8.5' => '8.5.3',
    ],

    /*
    |--------------------------------------------------------------------------
    | Binary Registry URL
    |--------------------------------------------------------------------------
    */
    'binary_registry_url' => env('NATIVEPHP_EXT_BINARY_REGISTRY', null),

    /*
    |--------------------------------------------------------------------------
    | Static PHP CLI Path
    |--------------------------------------------------------------------------
    |
    | This value determines the path where the static-php-cli installation
    | will be stored. The path will be resolved relative to the base path.
    |
    */
    'static_php_cli_path' => 'nativephp-php-custom',

    /*
    |--------------------------------------------------------------------------
    | Build Configuration
    |--------------------------------------------------------------------------
    |
    | These values determine the default configuration for building PHP
    | with extensions.
    |
    */
    'build' => [
        'sapi' => 'cli', // Supported values: 'cli', 'micro'
        'upx' => false,  // Whether to enable UPX compression by default
    ],

    // Supported PHP extensions
    'available_extensions' => [
        'bcmath',
        'bz2',
        'ctype',
        'curl',
        'dom',
        'fileinfo',
        'filter',
        'gd',
        'iconv',
        'mbstring',
        'opcache',
        'openssl',
        'pdo',
        'pdo_sqlite',
        'pdo_mysql',
        'pdo_pgsql',
        'phar',
        'session',
        'simplexml',
        'sockets',
        'sqlite3',
        'tokenizer',
        'xml',
        'zip',
        'zlib',
        'sqlsrv',
        'pdo_sqlsrv',
    ],

    // Required libraries for building extensions
    'required_libraries' => [
        'bzip2',
        'zlib',
        'openssl',
        'libssh2',
        'libiconv-win',
        'libxml2',
        'nghttp2',
        'curl',
        'libpng',
        'sqlite',
        'xz',
        'libzip',
    ],
];
