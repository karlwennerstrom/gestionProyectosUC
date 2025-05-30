<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filtro para super administradores
 * Sistema Multi-Área Universidad Católica
 */
class SuperAdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        
        // Verificar autenticación básica
        if (!$session->has('user_authenticated')) {
            return $this->handleUnauthenticated($request);
        }

        // Verificar si es super_admin
        $userType = $session->get('user_type');
        if ($userType !== 'super_admin') {
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
                'required_role' => 'super_admin'
            ])->setStatusCode(403);
        }

        return redirect()->to('/auth/access-denied');
    }
}