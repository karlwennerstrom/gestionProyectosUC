<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class SuperAdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        
        if (!$session->has('user_authenticated')) {
            return redirect()->to('/auth/login')->with('error', 'Debes iniciar sesión');
        }

        $userType = $session->get('user_type');
        if ($userType !== 'super_admin') {
            return redirect()->to('/auth/access-denied');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
