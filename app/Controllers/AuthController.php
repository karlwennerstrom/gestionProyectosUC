<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

/**
 * Controlador de Autenticación Simple
 * Sistema Multi-Área Universidad Católica
 */
class AuthController extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Mostrar formulario de login
     */
    public function login()
    {
        // Si ya está autenticado, redireccionar al dashboard
        if (session()->has('user_authenticated')) {
            return redirect()->to('/dashboard');
        }

        $data = [
            'title' => 'Iniciar Sesión - Sistema Multi-Área UC'
        ];

        return view('auth/simple_login', $data);
    }

    /**
     * Procesar login
     */
    public function processLogin()
    {
        // Validar datos del formulario
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // Debug: Mostrar datos recibidos
        log_message('debug', 'Login attempt - Email: ' . $email);

        // Validación básica
        if (empty($email) || empty($password)) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Email y contraseña son requeridos');
        }

        try {
            // Buscar usuario en la base de datos
            $user = $this->userModel->findByEmail($email);

            if (!$user) {
                log_message('debug', 'User not found: ' . $email);
                return redirect()->back()
                               ->withInput()
                               ->with('error', 'Usuario no encontrado');
            }

            // Verificar contraseña (temporal - siempre admin123)
            if ($password !== 'admin123') {
                log_message('debug', 'Invalid password for: ' . $email);
                return redirect()->back()
                               ->withInput()
                               ->with('error', 'Contraseña incorrecta');
            }

            // Verificar que el usuario esté activo
            if ($user['status'] !== 'active') {
                return redirect()->back()
                               ->withInput()
                               ->with('error', 'Usuario inactivo');
            }

            // Login exitoso - crear sesión
            $sessionData = [
                'user_authenticated' => true,
                'user_id' => $user['id'],
                'user_email' => $user['email'],
                'user_name' => $user['full_name'],
                'user_type' => $user['user_type'],
                'login_time' => time()
            ];

            session()->set($sessionData);

            // Actualizar último login en la BD
            $this->userModel->update($user['id'], [
                'last_login' => date('Y-m-d H:i:s'),
                'login_attempts' => 0
            ]);

            log_message('debug', 'Login successful for: ' . $email . ' as ' . $user['user_type']);

            // Redireccionar según tipo de usuario
            $redirectUrl = $this->getRedirectUrl($user['user_type']);
            
            return redirect()->to($redirectUrl)
                           ->with('success', 'Bienvenido ' . $user['full_name']);

        } catch (\Exception $e) {
            log_message('error', 'Login error: ' . $e->getMessage());
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error interno del sistema');
        }
    }

    /**
     * Obtener URL de redirección según tipo de usuario
     */
    private function getRedirectUrl(string $userType): string
    {
        switch ($userType) {
            case 'super_admin':
                return '/super-admin/dashboard';
                
            case 'admin':
                return '/admin/dashboard';
                
            case 'user':
            default:
                return '/dashboard';
        }
    }

    /**
     * Logout
     */
    public function logout()
    {
        $userName = session()->get('user_name');
        
        // Destruir sesión
        session()->destroy();
        
        return redirect()->to('/')
                       ->with('success', 'Hasta luego, ' . $userName);
    }

    /**
     * Verificar estado de autenticación (AJAX)
     */
    public function checkAuth()
    {
        $isAuthenticated = session()->has('user_authenticated');
        
        $response = [
            'authenticated' => $isAuthenticated,
            'user' => null
        ];

        if ($isAuthenticated) {
            $response['user'] = [
                'id' => session()->get('user_id'),
                'email' => session()->get('user_email'),
                'name' => session()->get('user_name'),
                'type' => session()->get('user_type')
            ];
        }

        return $this->response->setJSON($response);
    }

    /**
     * Página de acceso denegado
     */
    public function accessDenied()
    {
        $data = [
            'title' => 'Acceso Denegado',
            'message' => 'No tienes permisos para acceder a esta sección.',
            'user_type' => session()->get('user_type', 'unknown')
        ];

        return view('auth/access_denied', $data);
    }
}