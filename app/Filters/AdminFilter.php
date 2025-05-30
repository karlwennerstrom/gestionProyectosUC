<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filtro para administradores
 * Sistema Multi-Área Universidad Católica
 */
class AdminFilter implements FilterInterface
{
    /**
     * Ejecuta el filtro antes de la acción del controlador
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        
        // Verificar autenticación básica
        if (!$session->has('user_authenticated')) {
            return $this->handleUnauthenticated($request);
        }

        // Verificar si es admin o super_admin
        $userType = $session->get('user_type');
        if (!in_array($userType, ['admin', 'super_admin'])) {
            return $this->handleAccessDenied($request);
        }

        return null;
    }

    /**
     * Ejecuta el filtro después de la acción del controlador
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }

    /**
     * Maneja usuarios no autenticados
     */
    protected function handleUnauthenticated(RequestInterface $request)
    {
        // Para peticiones AJAX, devolver JSON
        if ($request->isAJAX()) {
            return service('response')->setJSON([
                'error' => 'No autenticado',
                'message' => 'Debes iniciar sesión como Administrador.',
                'required_role' => 'admin'
            ])->setStatusCode(401);
        }

        // Para peticiones normales, redireccionar al login
        return redirect()->to('/auth/login?return_url=' . urlencode(current_url()));
    }

    /**
     * Maneja acceso denegado por permisos insuficientes
     */
    protected function handleAccessDenied(RequestInterface $request)
    {
        // Para peticiones AJAX, devolver JSON
        if ($request->isAJAX()) {
            return service('response')->setJSON([
                'error' => 'Acceso denegado',
                'message' => 'Se requieren privilegios de Administrador.',
                'required_role' => 'admin',
                'current_role' => session()->get('user_type', 'unknown')
            ])->setStatusCode(403);
        }

        // Para peticiones normales, redireccionar a página de acceso denegado
        return redirect()->to('/auth/access-denied');
    }
}