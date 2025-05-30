<?php

namespace App\Libraries;

use Config\CAS as CASConfig;
use App\Models\UserModel;
use App\Models\AuditLogModel;
use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Librería de autenticación CAS
 * Sistema Multi-Área Universidad Católica
 */
class CASAuth
{
    /**
     * @var CASConfig Configuración CAS
     */
    protected CASConfig $config;

    /**
     * @var UserModel Modelo de usuarios
     */
    protected UserModel $userModel;

    /**
     * @var AuditLogModel Modelo de auditoría
     */
    protected AuditLogModel $auditModel;

    /**
     * @var \CodeIgniter\Session\Session Sesión
     */
    protected $session;

    /**
     * @var \CodeIgniter\HTTP\RequestInterface Request
     */
    protected $request;

    /**
     * @var array Datos del usuario autenticado
     */
    protected ?array $user = null;

    /**
     * @var bool Estado de autenticación
     */
    protected bool $authenticated = false;

    public function __construct()
    {
        $this->config = new CASConfig();
        $this->userModel = new UserModel();
        $this->auditModel = new AuditLogModel();
        $this->session = session();
        $this->request = service('request');

        // Verificar si hay sesión activa
        $this->checkExistingSession();
    }

    /**
     * Inicia el proceso de autenticación CAS
     */
    public function login(string $returnUrl = ''): void
    {
        // Limpiar cualquier sesión existente
        $this->logout(false);

        // Guardar URL de retorno en sesión
        if (!empty($returnUrl)) {
            $this->session->set('cas_return_url', $returnUrl);
        }

        // Generar URL de servicio
        $serviceUrl = base_url('/auth/cas-callback');
        
        // Redireccionar a CAS
        $loginUrl = $this->config->getLoginUrl($serviceUrl);
        
        // Log del intento de login
        $this->logAuditEvent('cas_login_attempt', null, [
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent(),
            'service_url' => $serviceUrl,
            'login_url' => $loginUrl
        ]);

        header('Location: ' . $loginUrl);
        exit;
    }

    /**
     * Maneja el callback de CAS después del login
     */
    public function handleCallback(): array
    {
        $ticket = $this->request->getGet('ticket');
        
        if (empty($ticket)) {
            throw new \Exception('No se recibió ticket de CAS');
        }

        // Validar ticket con el servidor CAS
        $validationResult = $this->validateTicket($ticket);

        if (!$validationResult['success']) {
            $this->logAuditEvent('cas_validation_failed', null, [
                'ticket' => substr($ticket, 0, 20) . '...',
                'error' => $validationResult['error'],
                'ip_address' => $this->request->getIPAddress()
            ]);
            
            throw new \Exception('Error validando ticket CAS: ' . $validationResult['error']);
        }

        // Procesar usuario autenticado
        $userData = $this->processAuthenticatedUser($validationResult['user'], $validationResult['attributes']);

        // Establecer sesión
        $this->setUserSession($userData);

        // Log de login exitoso
        $this->logAuditEvent('cas_login_success', $userData['id'], [
            'email' => $userData['email'],
            'full_name' => $userData['full_name'],
            'user_type' => $userData['user_type'],
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()
        ]);

        return $userData;
    }

    /**
     * Valida un ticket con el servidor CAS
     */
    protected function validateTicket(string $ticket): array
    {
        $serviceUrl = base_url('/auth/cas-callback');
        $validateUrl = $this->config->getValidateUrl();
        
        $params = [
            'service' => $serviceUrl,
            'ticket' => $ticket
        ];

        // Añadir formato para CAS 2.0 y 3.0
        if (in_array($this->config->casVersion, ['2.0', '3.0'])) {
            $params['format'] = 'XML';
        }

        $fullUrl = $validateUrl . '?' . http_build_query($params);

        try {
            // Realizar petición HTTP
            $client = \Config\Services::curlrequest($this->config->getHttpClientConfig());
            $response = $client->get($fullUrl);

            if ($response->getStatusCode() !== 200) {
                return [
                    'success' => false,
                    'error' => 'Error HTTP: ' . $response->getStatusCode()
                ];
            }

            $body = $response->getBody();

            // Parsear respuesta según versión CAS
            return $this->parseValidationResponse($body);

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error de conectividad: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Parsea la respuesta de validación del servidor CAS
     */
    protected function parseValidationResponse(string $response): array
    {
        if ($this->config->casVersion === '1.0') {
            return $this->parseCAS10Response($response);
        } else {
            return $this->parseCAS20Response($response);
        }
    }

    /**
     * Parsea respuesta CAS 1.0
     */
    protected function parseCAS10Response(string $response): array
    {
        $lines = explode("\n", trim($response));
        
        if (count($lines) >= 1 && trim($lines[0]) === 'yes') {
            $user = isset($lines[1]) ? trim($lines[1]) : '';
            return [
                'success' => true,
                'user' => $user,
                'attributes' => []
            ];
        }

        return [
            'success' => false,
            'error' => 'Validación CAS falló'
        ];
    }

    /**
     * Parsea respuesta CAS 2.0/3.0 XML
     */
    protected function parseCAS20Response(string $response): array
    {
        try {
            $xml = simplexml_load_string($response);
            
            if (!$xml) {
                return [
                    'success' => false,
                    'error' => 'Respuesta XML inválida'
                ];
            }

            // Registrar namespaces
            $xml->registerXPathNamespace('cas', 'http://www.yale.edu/tp/cas');

            // Verificar si la autenticación fue exitosa
            $success = $xml->xpath('//cas:authenticationSuccess');
            
            if (empty($success)) {
                $failure = $xml->xpath('//cas:authenticationFailure');
                $error = !empty($failure) ? (string)$failure[0] : 'Autenticación falló';
                
                return [
                    'success' => false,
                    'error' => $error
                ];
            }

            // Extraer usuario
            $userNodes = $xml->xpath('//cas:user');
            $user = !empty($userNodes) ? (string)$userNodes[0] : '';

            // Extraer atributos
            $attributes = [];
            $attributeNodes = $xml->xpath('//cas:attributes/*');
            
            foreach ($attributeNodes as $attr) {
                $attributes[$attr->getName()] = (string)$attr;
            }

            return [
                'success' => true,
                'user' => $user,
                'attributes' => $attributes
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error parseando XML: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Procesa el usuario autenticado y lo registra/actualiza en la BD
     */
    protected function processAuthenticatedUser(string $casUser, array $attributes): array
    {
        // Mapear atributos CAS a campos de usuario
        $mapping = $this->config->getUserAttributeMapping();
        $userData = [];

        foreach ($mapping as $casAttr => $dbField) {
            if (isset($attributes[$casAttr])) {
                $userData[$dbField] = $attributes[$casAttr];
            }
        }

        // El email puede venir como usuario principal o atributo
        $email = $userData['email'] ?? $casUser;
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Si no es email válido, intentar construirlo
            $email = $casUser . '@uc.cl';
        }

        $userData['email'] = strtolower($email);

        // Buscar usuario existente
        $existingUser = $this->userModel->where('email', $userData['email'])->first();

        if ($existingUser) {
            // Actualizar último login y datos si cambió algo
            $updateData = [
                'last_login' => date('Y-m-d H:i:s'),
                'login_attempts' => 0,
            ];

            // Actualizar nombre si cambió
            if (!empty($userData['full_name']) && $userData['full_name'] !== $existingUser['full_name']) {
                $updateData['full_name'] = $userData['full_name'];
            }

            $this->userModel->update($existingUser['id'], $updateData);
            
            // Combinar datos existentes con nuevos
            $userData = array_merge($existingUser, $updateData, $userData);
            $userData['id'] = $existingUser['id'];

        } else {
            // Verificar si el email está autorizado
            if (!$this->isEmailAuthorized($userData['email'])) {
                throw new \Exception('Email no autorizado para acceder al sistema: ' . $userData['email']);
            }

            // Crear nuevo usuario
            $newUserData = [
                'email' => $userData['email'],
                'full_name' => $userData['full_name'] ?? $casUser,
                'user_type' => 'user', // Por defecto es usuario normal
                'status' => 'active',
                'last_login' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $userId = $this->userModel->insert($newUserData);
            $userData = array_merge($newUserData, ['id' => $userId]);
        }

        return $userData;
    }

    /**
     * Verifica si un email está autorizado para acceder al sistema
     */
    protected function isEmailAuthorized(string $email): bool
    {
        // Por ahora, todos los emails @uc.cl están autorizados
        // En el futuro se puede implementar una lista de emails autorizados
        return str_ends_with(strtolower($email), '@uc.cl');
    }

    /**
     * Establece la sesión del usuario
     */
    protected function setUserSession(array $userData): void
    {
        $sessionData = [
            'cas_user_id' => $userData['id'],
            'cas_email' => $userData['email'],
            'cas_full_name' => $userData['full_name'],
            'cas_user_type' => $userData['user_type'],
            'cas_authenticated' => true,
            'cas_login_time' => time(),
            'cas_last_activity' => time(),
        ];

        $this->session->set($sessionData);
        $this->user = $userData;
        $this->authenticated = true;
    }

    /**
     * Verifica si hay una sesión CAS existente
     */
    protected function checkExistingSession(): void
    {
        if ($this->session->has('cas_authenticated') && $this->session->get('cas_authenticated')) {
            // Verificar timeout de sesión
            $lastActivity = $this->session->get('cas_last_activity');
            $currentTime = time();

            if (($currentTime - $lastActivity) > $this->config->sessionTimeout) {
                $this->logout(false);
                return;
            }

            // Actualizar última actividad
            $this->session->set('cas_last_activity', $currentTime);

            // Cargar datos del usuario
            $userId = $this->session->get('cas_user_id');
            if ($userId) {
                $this->user = $this->userModel->find($userId);
                $this->authenticated = true;
            }
        }
    }

    /**
     * Cierra la sesión CAS
     */
    public function logout(bool $redirectToCAS = true): void
    {
        // Log del logout
        if ($this->authenticated && $this->user) {
            $this->logAuditEvent('cas_logout', $this->user['id'], [
                'email' => $this->user['email'],
                'session_duration' => time() - $this->session->get('cas_login_time', time()),
                'ip_address' => $this->request->getIPAddress()
            ]);
        }

        // Limpiar sesión
        $this->session->remove([
            'cas_user_id',
            'cas_email', 
            'cas_full_name',
            'cas_user_type',
            'cas_authenticated',
            'cas_login_time',
            'cas_last_activity',
            'cas_return_url'
        ]);

        $this->user = null;
        $this->authenticated = false;

        // Redireccionar a logout de CAS si se solicita
        if ($redirectToCAS) {
            $logoutUrl = $this->config->getLogoutUrl();
            header('Location: ' . $logoutUrl);
            exit;
        }
    }

    /**
     * Verifica si el usuario está autenticado
     */
    public function isAuthenticated(): bool
    {
        return $this->authenticated;
    }

    /**
     * Obtiene los datos del usuario autenticado
     */
    public function getUser(): ?array
    {
        return $this->user;
    }

    /**
     * Obtiene el ID del usuario autenticado
     */
    public function getUserId(): ?int
    {
        return $this->user['id'] ?? null;
    }

    /**
     * Verifica si el usuario tiene un tipo específico
     */
    public function hasUserType(string $userType): bool
    {
        return $this->user && $this->user['user_type'] === $userType;
    }

    /**
     * Verifica si el usuario es super administrador
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasUserType('super_admin');
    }

    /**
     * Verifica si el usuario es administrador de área
     */
    public function isAdmin(): bool
    {
        return $this->hasUserType('admin');
    }

    /**
     * Verifica si el usuario es usuario normal
     */
    public function isUser(): bool
    {
        return $this->hasUserType('user');
    }

    /**
     * Obtiene la URL de retorno guardada
     */
    public function getReturnUrl(): string
    {
        $returnUrl = $this->session->get('cas_return_url');
        $this->session->remove('cas_return_url');
        
        return $returnUrl ?: $this->config->loginSuccessUrl;
    }

    /**
     * Fuerza la re-autenticación
     */
    public function forceReauth(): void
    {
        $this->logout(false);
        $this->login();
    }

    /**
     * Registra eventos de auditoría
     */
    protected function logAuditEvent(string $action, ?int $userId = null, array $details = []): void
    {
        try {
            $logData = [
                'user_id' => $userId,
                'action' => $action,
                'entity_type' => 'auth',
                'description' => $this->getActionDescription($action),
                'ip_address' => $details['ip_address'] ?? $this->request->getIPAddress(),
                'user_agent' => $details['user_agent'] ?? $this->request->getUserAgent(),
                'session_id' => session_id(),
                'success' => !str_contains($action, 'failed'),
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Agregar detalles específicos
            if (!empty($details)) {
                unset($details['ip_address'], $details['user_agent']); // Ya están en campos separados
                $logData['new_values'] = json_encode($details);
            }

            $this->auditModel->insert($logData);
        } catch (\Exception $e) {
            log_message('error', 'Error registrando evento de auditoría CAS: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene descripción para acciones de auditoría
     */
    protected function getActionDescription(string $action): string
    {
        $descriptions = [
            'cas_login_attempt' => 'Intento de login vía CAS',
            'cas_login_success' => 'Login exitoso vía CAS',
            'cas_validation_failed' => 'Falló la validación de ticket CAS',
            'cas_logout' => 'Logout vía CAS',
        ];

        return $descriptions[$action] ?? 'Evento CAS: ' . $action;
    }

    /**
     * Obtiene información de debug de la configuración
     */
    public function getDebugInfo(): array
    {
        if (!$this->config->debugMode) {
            return ['debug_disabled' => true];
        }

        return [
            'config' => $this->config->getDebugInfo(),
            'session' => [
                'authenticated' => $this->authenticated,
                'user_id' => $this->getUserId(),
                'session_data' => $this->session->get()
            ],
            'validation_errors' => $this->config->validate()
        ];
    }
}