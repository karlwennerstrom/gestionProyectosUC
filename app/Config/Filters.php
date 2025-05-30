<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\SecureHeaders;
use App\Filters\AuthFilter;
use App\Filters\AdminFilter;
use App\Filters\SuperAdminFilter;

/**
 * Configuración de filtros del sistema - SIMPLIFICADA
 * Sistema Multi-Área Universidad Católica
 */
class Filters extends BaseConfig
{
    /**
     * Configura aliases para los filtros
     */
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        
        // Filtros de autenticación
        'auth'          => AuthFilter::class,
        'admin'         => AdminFilter::class,
        'super_admin'   => SuperAdminFilter::class,
    ];

    /**
     * Lista de filtros que deben ejecutarse ANTES de todos los demás
     */
    public array $globals = [
        'before' => [
            // Filtros de seguridad básica
            'honeypot',
            'csrf' => ['except' => [
                'auth/cas-callback',
                'auth/single-logout',
                'auth/healthcheck',
                'auth/process-login',  // Añadido
                'status',
                'info'
            ]],
            'invalidchars',
        ],
        'after' => [
            'toolbar',
            'secureheaders',
        ],
    ];

    /**
     * Lista de filtros basados en métodos HTTP
     */
    public array $methods = [
        'post' => ['csrf'],
        'get'  => [],
    ];

    /**
     * Lista de filtros que se aplican a URIs específicas
     */
    public array $filters = [
        // Rutas que requieren autenticación básica
        'auth' => [
            'before' => [
                'dashboard',
                'dashboard/*',
                'projects/*',
                'profile/*',
                'notifications/*',
            ],
        ],

        // Rutas que requieren permisos de administrador
        'admin' => [
            'before' => [
                'admin/*',
            ],
        ],

        // Rutas que requieren permisos de super administrador
        'super_admin' => [
            'before' => [
                'super-admin/*',
            ],
        ],
    ];

    /**
     * Lista de filtros especiales que requieren configuración adicional
     */
    public array $required = [
        'before' => [],
        'after'  => [],
    ];

    public function __construct()
    {
        parent::__construct();

        // Configuración específica por entorno
        $this->configureByEnvironment();
    }

    /**
     * Configura filtros específicos según el entorno
     */
    private function configureByEnvironment(): void
    {
        $environment = ENVIRONMENT;

        switch ($environment) {
            case 'development':
                // En desarrollo, habilitar toolbar de debug
                if (!in_array('toolbar', $this->globals['after'])) {
                    $this->globals['after'][] = 'toolbar';
                }
                break;

            case 'testing':
                // En testing, deshabilitar algunos filtros
                $this->globals['before'] = array_filter(
                    $this->globals['before'],
                    fn($filter) => !in_array($filter, ['honeypot'])
                );
                break;

            case 'production':
                // En producción, agregar filtros de seguridad adicionales
                $this->globals['before'] = array_merge(
                    ['secureheaders'],
                    $this->globals['before']
                );
                break;
        }
    }

    /**
     * Valida la configuración de filtros
     */
    public function validate(): array
    {
        $errors = [];

        // Verificar que todos los alias existan
        foreach ($this->aliases as $alias => $class) {
            if (!class_exists($class)) {
                $errors[] = "Clase de filtro no encontrada: {$class} (alias: {$alias})";
            }
        }

        return $errors;
    }

    /**
     * Obtiene información de debug sobre filtros
     */
    public function getDebugInfo(): array
    {
        return [
            'aliases' => array_keys($this->aliases),
            'globals' => $this->globals,
            'methods' => $this->methods,
            'filters' => array_keys($this->filters),
            'validation_errors' => $this->validate(),
            'environment' => ENVIRONMENT,
        ];
    }
}