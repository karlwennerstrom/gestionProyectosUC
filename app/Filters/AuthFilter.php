<?php

namespace App\Filters;

use App\Libraries\CASAuth;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filtro de autenticación CAS
 * Sistema Multi-Área Universidad Católica
 */
class AuthFilter implements FilterInterface
{
    /**
     * @var CASAuth Librería de autenticación CAS
     */
    protected CASAuth $casAuth;

    public function __construct()
    {
        $this->casAuth = new CASAuth();
    }

    /**
     * Ejecuta el filtro antes de la acción del controlador
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Verificar si el usuario está autenticado
        if (!$this->casAuth->isAuthenticated()) {
            return $this->handleUnauthenticated($request);
        }

        // Verificar permisos específicos si se proporcionaron argumentos
        if ($arguments && !$this->checkPermissions($arguments)) {
            return $this->handleAccessDenied($request);
        }

        // Continuar con la ejecución normal
        return null;
    }

    /**
     * Ejecuta el filtro después de la acción del controlador
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No se necesita procesamiento posterior por ahora
        return $response;
    }

    /**
     * Maneja usuarios no autenticados
     */
    protected function handleUnauthenticated(RequestInterface $request)
    {
        // Para peticiones AJAX, devolver JSON
        if ($request->isAJAX()) {
            $response = service('response');
            return $response->setJSON([
                'error' => 'No autenticado',
                'message' => 'Debes iniciar sesión para acceder a este recurso.',
                'login_url' => base_url('/auth/login')
            ])->setStatusCode(401);
        }

        // Para peticiones normales, guardar URL de destino y redireccionar al login
        $currentUrl = current_url();
        $returnUrl = $currentUrl !== base_url() ? $currentUrl : '';

        // Construir URL de login con parámetro de retorno
        $loginUrl = base_url('/auth/login');
        if ($returnUrl) {
            $loginUrl .= '?return_url=' . urlencode($returnUrl);
        }

        return redirect()->to($loginUrl);
    }

    /**
     * Maneja acceso denegado por permisos insuficientes
     */
    protected function handleAccessDenied(RequestInterface $request)
    {
        // Para peticiones AJAX, devolver JSON
        if ($request->isAJAX()) {
            $response = service('response');
            return $response->setJSON([
                'error' => 'Acceso denegado',
                'message' => 'No tienes permisos para acceder a este recurso.',
                'user_type' => $this->casAuth->getUser()['user_type'] ?? 'unknown'
            ])->setStatusCode(403);
        }

        // Para peticiones normales, redireccionar a página de acceso denegado
        return redirect()->to('/auth/access-denied');
    }

    /**
     * Verifica permisos específicos
     */
    protected function checkPermissions($arguments): bool
    {
        if (!is_array($arguments)) {
            $arguments = [$arguments];
        }

        $user = $this->casAuth->getUser();
        $userType = $user['user_type'] ?? null;

        foreach ($arguments as $permission) {
            switch ($permission) {
                case 'super_admin':
                    return $userType === 'super_admin';

                case 'admin':
                    return in_array($userType, ['super_admin', 'admin']);

                case 'user':
                    return in_array($userType, ['super_admin', 'admin', 'user']);

                case 'admin_only':
                    return $userType === 'admin';

                case 'user_only':
                    return $userType === 'user';

                default:
                    // Permiso personalizado no reconocido
                    log_message('warning', 'Permiso no reconocido en AuthFilter: ' . $permission);
                    return false;
            }
        }

        return true;
    }
}

/**
 * Filtro específico para Super Administradores
 */
class SuperAdminFilter implements FilterInterface
{
    protected CASAuth $casAuth;

    public function __construct()
    {
        $this->casAuth = new CASAuth();
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        if (!$this->casAuth->isAuthenticated()) {
            return $this->handleUnauthenticated($request);
        }

        if (!$this->casAuth->isSuperAdmin()) {
            return $this->handleAccessDenied($request);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }

    protected function handleUnauthenticated(RequestInterface $request)
    {
        if ($request->isAJAX()) {
            return service('response')->setJSON([
                'error' => 'No autenticado',
                'message' => 'Debes iniciar sesión como Super Administrador.',
                'required_role' => 'super_admin'
            ])->setStatusCode(401);
        }

        return redirect()->to('/auth/login?return_url=' . urlencode(current_url()));
    }

    protected function handleAccessDenied(RequestInterface $request)
    {
        if ($request->isAJAX()) {
            return service('response')->setJSON([
                'error' => 'Acceso denegado',
                'message' => 'Se requieren privilegios de Super Administrador.',
                'required_role' => 'super_admin',
                'current_role' => $this->casAuth->getUser()['user_type'] ?? 'unknown'
            ])->setStatusCode(403);
        }

        return redirect()->to('/auth/access-denied');
    }
}

/**
 * Filtro específico para Administradores de Área
 */
class AdminFilter implements FilterInterface
{
    protected CASAuth $casAuth;

    public function __construct()
    {
        $this->casAuth = new CASAuth();
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        if (!$this->casAuth->isAuthenticated()) {
            return $this->handleUnauthenticated($request);
        }

        // Permitir acceso a super_admin y admin
        if (!$this->casAuth->isSuperAdmin() && !$this->casAuth->isAdmin()) {
            return $this->handleAccessDenied($request);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }

    protected function handleUnauthenticated(RequestInterface $request)
    {
        if ($request->isAJAX()) {
            return service('response')->setJSON([
                'error' => 'No autenticado',
                'message' => 'Debes iniciar sesión como Administrador.',
                'required_role' => 'admin'
            ])->setStatusCode(401);
        }

        return redirect()->to('/auth/login?return_url=' . urlencode(current_url()));
    }

    protected function handleAccessDenied(RequestInterface $request)
    {
        if ($request->isAJAX()) {
            return service('response')->setJSON([
                'error' => 'Acceso denegado',
                'message' => 'Se requieren privilegios de Administrador.',
                'required_role' => 'admin',
                'current_role' => $this->casAuth->getUser()['user_type'] ?? 'unknown'
            ])->setStatusCode(403);
        }

        return redirect()->to('/auth/access-denied');
    }
}

/**
 * Filtro para verificar permisos de área específica
 */
class AreaPermissionFilter implements FilterInterface
{
    protected CASAuth $casAuth;

    public function __construct()
    {
        $this->casAuth = new CASAuth();
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        if (!$this->casAuth->isAuthenticated()) {
            return $this->handleUnauthenticated($request);
        }

        // Super admin tiene acceso a todo
        if ($this->casAuth->isSuperAdmin()) {
            return null;
        }

        // Verificar si es admin de área específica
        if ($this->casAuth->isAdmin() && $arguments) {
            $areaId = is_array($arguments) ? $arguments[0] : $arguments;
            
            if ($this->checkAreaPermission($areaId)) {
                return null;
            }
        }

        return $this->handleAccessDenied($request);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }

    protected function checkAreaPermission($areaId): bool
    {
        // Verificar en la base de datos si el usuario es admin de esta área
        $areaAdminModel = new \App\Models\AreaAdminModel();
        $userId = $this->casAuth->getUserId();

        $assignment = $areaAdminModel
            ->where('user_id', $userId)
            ->where('area_id', $areaId)
            ->first();

        return !empty($assignment);
    }

    protected function handleUnauthenticated(RequestInterface $request)
    {
        if ($request->isAJAX()) {
            return service('response')->setJSON([
                'error' => 'No autenticado',
                'message' => 'Debes iniciar sesión.',
                'required_role' => 'area_admin'
            ])->setStatusCode(401);
        }

        return redirect()->to('/auth/login?return_url=' . urlencode(current_url()));
    }

    protected function handleAccessDenied(RequestInterface $request)
    {
        if ($request->isAJAX()) {
            return service('response')->setJSON([
                'error' => 'Acceso denegado',
                'message' => 'No tienes permisos para administrar esta área.',
                'required_role' => 'area_admin'
            ])->setStatusCode(403);
        }

        return redirect()->to('/auth/access-denied');
    }
}

/**
 * Filtro de mantenimiento
 */
class MaintenanceFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Verificar si el sistema está en mantenimiento
        $maintenanceMode = $_ENV['MAINTENANCE_MODE'] ?? false;
        
        if (filter_var($maintenanceMode, FILTER_VALIDATE_BOOLEAN)) {
            // Permitir acceso a rutas de autenticación y mantenimiento
            $allowedPaths = ['/auth/', '/maintenance', '/healthcheck'];
            $currentPath = $request->getUri()->getPath();
            
            foreach ($allowedPaths as $path) {
                if (str_starts_with($currentPath, $path)) {
                    return null;
                }
            }

            // Permitir acceso a super admins
            $casAuth = new CASAuth();
            if ($casAuth->isAuthenticated() && $casAuth->isSuperAdmin()) {
                return null;
            }

            // Redireccionar a página de mantenimiento
            return redirect()->to('/auth/maintenance');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}