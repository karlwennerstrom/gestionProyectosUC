<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Logger extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Error Logging Threshold
     * --------------------------------------------------------------------------
     * You can enable error logging by setting a threshold over zero. The
     * threshold determines what gets logged. Any log message with a level
     * below the threshold will not be logged.
     * 
     * Threshold options are:
     * 0 = Disables logging, Error logging TURNED OFF
     * 1 = Emergency Messages  (System is unusable)
     * 2 = Alert Messages      (Action Must Be Taken Immediately)
     * 3 = Critical Messages   (Application component unavailable, unexpected exception)
     * 4 = Error Messages      (Runtime errors)
     * 5 = Warning Messages    (Use of deprecated procedures, poor use of an API)
     * 6 = Notice Messages     (Program flow)
     * 7 = Informational Messages (Most detailed)
     * 8 = Debug Messages      (Most detailed)
     * 9 = All Messages
     */
    public int $threshold = 9; // Log everything in development

    /**
     * --------------------------------------------------------------------------
     * Date Format for Logs
     * --------------------------------------------------------------------------
     * Each item that is logged has an associated date. You can use PHP date
     * codes to set your own date formatting
     */
    public string $dateFormat = 'Y-m-d H:i:s';

    /**
     * --------------------------------------------------------------------------
     * Log Handlers
     * --------------------------------------------------------------------------
     * The logging system supports multiple actions to be taken when something
     * is logged. This is done by allowing for multiple Handlers, special classes
     * designed to write the log to their chosen destinations, whether that is
     * a file on the getServer, a cloud-based service, or even taking actions
     * such as emailing the dev team.
     *
     * Each handler is defined by the class name used for that handler, and it
     * should implement the `Psr\Log\LoggerInterface`.
     *
     * The value of each key is an array that should contain the 'class' element,
     * as well as any custom elements that each handler uses. The 'class' element
     * should be the class name or a string that the `Services::locator()`
     * can find.
     *
     * NOTE: If you change the handler classes,
     *       make sure the autoloader can find the new classes.
     */
    public array $handlers = [
        /**
         * File Handler
         */
        'CodeIgniter\Log\Handlers\FileHandler' => [
            // The log levels this handler will handle.
            'handles' => [
                'critical',
                'alert',
                'emergency',
                'debug',
                'error',
                'info',
                'notice',
                'warning',
            ],

            /**
             * The default filename extension for log files.
             * An extension of 'php' allows for protecting the log files
             * via basic scripting, when they are to be stored under a publicly
             * accessible directory.
             * 
             * Note: Leaving it blank will default to 'log'.
             */
            'fileExtension' => 'log',

            /**
             * The file system permissions to be applied on newly created log files.
             *
             * IMPORTANT: This MUST be an integer (no quotes) and you MUST use octal
             *            integer notation (i.e. 0700, 0644, etc.)
             */
            'filePermissions' => 0644,

            /**
             * Logging Directory Path
             *
             * By default, logs are written to WRITEPATH . 'logs/'
             * Specify a different directory here, if needed.
             */
            'path' => WRITEPATH . 'logs/',
        ],
    ];
}