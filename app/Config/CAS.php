<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Configuración de autenticación CAS
 * Sistema Multi-Área Universidad Católica
 */
class CAS extends BaseConfig
{
    /**
     * Servidor CAS de la Universidad Católica
     */
    public string $casServer = 'sso-lib.uc.cl';

    /**
     * Puerto del servidor CAS
     */
    public int $casPort = 443;

    /**
     * URI del servidor CAS
     */
    public string $casUri = '/cas';

    /**
     * Versión del protocolo CAS
     */
    public string $casVersion = '3.0';

    /**
     * Habilitar modo debug
     */
    public bool $debugMode = false;

    /**
     * Certificado SSL personalizado (opcional)
     */
    public ?string $caCertPath = null;

    /**
     * Verificar certificados SSL
     */
    public bool $verifySSL = true;

    /**
     * Tiempo de expiración de sesión en segundos
     */
    public int $sessionTimeout = 7200; // 2 horas

    /**
     * Nombre de la cookie de sesión
     */
    public string $sessionCookieName = 'uc_cas_session';

    /**
     * Atributos de usuario que se obtienen del CAS
     */
    public array $userAttributes = [
        'email',
        'cn',           // Common Name (nombre completo)
        'sn',           // Surname (apellido)
        'givenName',    // Nombre
        'ou',           // Organizational Unit (unidad organizacional)
        'title',        // Título o cargo
        'telephoneNumber',
        'department',
    ];

    /**
     * URL de redirección después del login exitoso
     */
    public string $loginSuccessUrl = '/dashboard';

    /**
     * URL de redirección después del logout
     */
    public string $logoutUrl = '/';

    /**
     * URL de la aplicación (se usa para generar service URLs)
     */
    public string $serviceUrl = '';

    /**
     * Habilitar Single Sign Out (logout desde CAS cierra sesión en aplicación)
     */
    public bool $enableSingleLogout = true;

    /**
     * Prefijo para logs de CAS
     */
    public string $logPrefix = 'CAS';

    /**
     * Nivel de log para eventos CAS
     */
    public string $logLevel = 'info';

    public function __construct()
    {
        parent::__construct();

        // Cargar configuración desde variables de entorno
        $this->loadEnvironmentConfig();

        // Configurar URLs según el entorno
        $this->configureUrls();

        // Configurar modo debug según el entorno
        $this->configureDebugMode();
    }

    /**
     * Carga la configuración CAS desde variables de entorno
     */
    private function loadEnvironmentConfig(): void
    {
        $this->casServer = $_ENV['CAS_SERVER'] ?? $this->casServer;
        $this->casPort = (int)($_ENV['CAS_PORT'] ?? $this->casPort);
        $this->casUri = $_ENV['CAS_URI'] ?? $this->casUri;
        $this->casVersion = $_ENV['CAS_VERSION'] ?? $this->casVersion;
        $this->caCertPath = $_ENV['CAS_CA_CERT_PATH'] ?? $this->caCertPath;
        $this->verifySSL = filter_var($_ENV['CAS_VERIFY_SSL'] ?? $this->verifySSL, FILTER_VALIDATE_BOOLEAN);
        $this->sessionTimeout = (int)($_ENV['CAS_SESSION_TIMEOUT'] ?? $this->sessionTimeout);
        $this->enableSingleLogout = filter_var($_ENV['CAS_ENABLE_SLO'] ?? $this->enableSingleLogout, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Configura las URLs según el entorno
     */
    private function configureUrls(): void
    {
        $baseUrl = $_ENV['app.baseURL'] ?? base_url();
        
        // Asegurar que la URL base termine sin slash
        $baseUrl = rtrim($baseUrl, '/');

        $this->serviceUrl = $baseUrl;
        $this->loginSuccessUrl = $baseUrl . $this->loginSuccessUrl;
        $this->logoutUrl = $baseUrl . $this->logoutUrl;
    }

    /**
     * Configura el modo debug según el entorno
     */
    private function configureDebugMode(): void
    {
        $environment = $_ENV['CI_ENVIRONMENT'] ?? 'production';
        
        $this->debugMode = ($environment === 'development');
        
        if ($this->debugMode) {
            $this->logLevel = 'debug';
            $this->verifySSL = false; // Para desarrollo local
        }
    }

    /**
     * Obtiene la URL completa del servidor CAS
     */
    public function getCasServerUrl(): string
    {
        $protocol = ($this->casPort === 443) ? 'https' : 'http';
        $port = (($this->casPort === 443 && $protocol === 'https') || 
                 ($this->casPort === 80 && $protocol === 'http')) ? '' : ':' . $this->casPort;
        
        return $protocol . '://' . $this->casServer . $port . $this->casUri;
    }

    /**
     * Obtiene la URL de login de CAS
     */
    public function getLoginUrl(string $service = ''): string
    {
        $service = $service ?: $this->serviceUrl . '/auth/cas-callback';
        return $this->getCasServerUrl() . '/login?service=' . urlencode($service);
    }

    /**
     * Obtiene la URL de logout de CAS
     */
    public function getLogoutUrl(string $url = ''): string
    {
        $url = $url ?: $this->logoutUrl;
        return $this->getCasServerUrl() . '/logout?url=' . urlencode($url);
    }

    /**
     * Obtiene la URL de validación de ticket
     */
    public function getValidateUrl(): string
    {
        $endpoint = match ($this->casVersion) {
            '1.0' => '/validate',
            '2.0' => '/serviceValidate',
            '3.0' => '/p3/serviceValidate',
            default => '/serviceValidate'
        };

        return $this->getCasServerUrl() . $endpoint;
    }

    /**
     * Valida la configuración CAS
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->casServer)) {
            $errors[] = 'CAS Server no configurado';
        }

        if ($this->casPort < 1 || $this->casPort > 65535) {
            $errors[] = 'Puerto CAS inválido';
        }

        if (empty($this->casUri)) {
            $errors[] = 'URI CAS no configurado';
        }

        if (!in_array($this->casVersion, ['1.0', '2.0', '3.0'])) {
            $errors[] = 'Versión CAS no soportada';
        }

        if (empty($this->serviceUrl)) {
            $errors[] = 'URL de servicio no configurada';
        }

        // Verificar conectividad con servidor CAS (opcional)
        if ($this->debugMode && !empty($this->casServer)) {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'method' => 'HEAD'
                ]
            ]);
            
            $headers = @get_headers($this->getCasServerUrl(), 1, $context);
            if (!$headers) {
                $errors[] = 'No se puede conectar al servidor CAS: ' . $this->casServer;
            }
        }

        return $errors;
    }

    /**
     * Obtiene información de configuración para debug
     */
    public function getDebugInfo(): array
    {
        return [
            'cas_server' => $this->casServer,
            'cas_port' => $this->casPort,
            'cas_uri' => $this->casUri,
            'cas_version' => $this->casVersion,
            'server_url' => $this->getCasServerUrl(),
            'login_url' => $this->getLoginUrl(),
            'logout_url' => $this->getLogoutUrl(),
            'validate_url' => $this->getValidateUrl(),
            'service_url' => $this->serviceUrl,
            'debug_mode' => $this->debugMode,
            'verify_ssl' => $this->verifySSL,
            'session_timeout' => $this->sessionTimeout,
            'user_attributes' => $this->userAttributes,
        ];
    }

    /**
     * Obtiene atributos de mapeo para usuarios
     * Mapea atributos CAS a campos de la base de datos
     */
    public function getUserAttributeMapping(): array
    {
        return [
            'email' => 'email',
            'cn' => 'full_name',
            'givenName' => 'first_name',
            'sn' => 'last_name',
            'ou' => 'department',
            'title' => 'job_title',
            'telephoneNumber' => 'phone',
        ];
    }

    /**
     * Obtiene la configuración para el cliente HTTP
     */
    public function getHttpClientConfig(): array
    {
        return [
            'timeout' => 30,
            'verify' => $this->verifySSL,
            'cert' => $this->caCertPath,
            'headers' => [
                'User-Agent' => 'UC-MultiArea-System/1.0',
                'Accept' => 'application/xml, text/xml, */*',
            ],
        ];
    }
}