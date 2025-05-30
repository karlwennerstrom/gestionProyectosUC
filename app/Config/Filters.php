<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\SecureHeaders;

class Filters extends BaseConfig
{
    /**
     * Configures aliases for Filter classes to
     * make reading things nicer and simpler.
     */
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        
        // Filtros personalizados del sistema
        'auth'          => \App\Filters\AuthFilter::class,
        'admin'         => \App\Filters\AdminFilter::class,
        'super_admin'   => \App\Filters\SuperAdminFilter::class,
    ];

    /**
     * List of filter aliases that are always
     * applied before and after every request.
     */
    public array $globals = [
        'before' => [
            'honeypot',
            'csrf' => ['except' => [
                'api/*',  // Excluir CSRF para APIs
                'status', // Excluir para endpoint de estado
                'info'    // Excluir para endpoint de información
            ]],
            'invalidchars',
        ],
        'after' => [
            'toolbar',
            'secureheaders',
        ],
    ];

    /**
     * List of filter aliases that works on a
     * particular HTTP method (GET, POST, etc.).
     *
     * Example:
     * 'POST' => ['foo', 'bar']
     *
     * If you use this, you should disable auto-routing because auto-routing
     * permits any HTTP method to access a controller. Accessing the controller
     * with a method you don't expect could bypass the filter.
     */
    public array $methods = [];

    /**
     * List of filter aliases that should run on any
     * before or after URI patterns.
     *
     * Example:
     * 'isLoggedIn' => ['before' => ['account/*', 'profiles/*']]
     */
    public array $filters = [
        // Filtro de autenticación básica
        'auth' => [
            'before' => [
                'dashboard',
                'dashboard/*',
                'projects',
                'projects/*',
                'documents',
                'documents/*',
                'notifications',
                'notifications/*',
                'reports',
                'reports/*',
                'admin',
                'admin/*',
                'super-admin',
                'super-admin/*',
                'api/notifications/*',
                'api/projects/*',
                'api/documents/*'
            ]
        ],
        
        // Filtro de administrador de área
        'admin' => [
            'before' => [
                'admin',
                'admin/*'
            ]
        ],
        
        // Filtro de super administrador
        'super_admin' => [
            'before' => [
                'super-admin',
                'super-admin/*'
            ]
        ]
    ];
}