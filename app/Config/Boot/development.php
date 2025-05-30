<?php

/*
 |--------------------------------------------------------------------------
 | DEBUG MODE
 |--------------------------------------------------------------------------
 | Debug mode is an experimental flag that can allow for displaying of
 | additional debug toolbars and information during development.
 | This is not used in production.
 | It can be set to 'true' or 'false'.
 */
defined('CI_DEBUG') || define('CI_DEBUG', true);

/*
 |--------------------------------------------------------------------------
 | ERROR REPORTING
 |--------------------------------------------------------------------------
 | Different environments will require different levels of error reporting.
 | By default development will show errors but testing and live will hide them.
 */

// Show all errors in development
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);

// Configurar logging
ini_set('error_log', WRITEPATH . 'logs/php_errors.log');

/*
 |--------------------------------------------------------------------------
 | DEBUG BACKTRACE
 |--------------------------------------------------------------------------
 | If true, this constant will tell the error screens to display debug
 | backtraces along with the other error information. If you would
 | prefer to not see this, set this value to false.
 */
defined('SHOW_DEBUG_BACKTRACE') || define('SHOW_DEBUG_BACKTRACE', true);

/*
 |--------------------------------------------------------------------------
 | ENVIRONMENT
 |--------------------------------------------------------------------------
 | You can load different configurations depending on your
 | current environment. Setting the environment also influences
 | things like logging and error reporting.
 | This can be set to anything, but default usage is:
 *     development
 *     testing
 *     production
 */
defined('ENVIRONMENT') || define('ENVIRONMENT', 'development');