<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

/**
 * Controlador Principal del Sistema - SIMPLIFICADO
 * Sistema Multi-Área Universidad Católica
 */
class HomeController extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Página principal / Landing page
     */
    public function index()
    {
        $session = session();
        
        // Si está autenticado, redireccionar al dashboard
        if ($session->has('user_authenticated')) {
            return redirect()->to('/dashboard');
        }

        // Página principal para usuarios no autenticados
        $data = [
            'title' => 'Sistema Multi-Área UC',
            'description' => 'Sistema de gestión de proyectos con flujo de aprobaciones por áreas especializadas',
            'show_login' => true,
            'environment' => ENVIRONMENT
        ];

        return view('home/index', $data);
    }

    /**
     * Dashboard principal (requiere autenticación)
     */
    public function dashboard()
    {
        $session = session();
        
        // Verificar autenticación
        if (!$session->has('user_authenticated')) {
            return redirect()->to('/auth/login');
        }

        $userType = $session->get('user_type', 'user');
        
        // Para usuarios normales, mostrar su dashboard
        if ($userType === 'user') {
            return $this->userDashboard();
        }
        
        // Para otros tipos, redireccionar a sus dashboards específicos
        switch ($userType) {
            case 'super_admin':
                return redirect()->to('/super-admin/dashboard');
                
            case 'admin':
                return redirect()->to('/admin/dashboard');
                
            default:
                return $this->userDashboard();
        }
    }

    /**
     * Dashboard para usuarios normales
     */
    protected function userDashboard()
{
    $session = session();
    
    $userData = [
        'id' => $session->get('user_id'),
        'email' => $session->get('user_email'),
        'full_name' => $session->get('user_name'),
        'user_type' => $session->get('user_type')
    ];
    
    $data = [
        'title' => 'Dashboard Usuario - Sistema Multi-Área UC',
        'navbar_type' => 'dashboard',
        'user' => $userData,
        'stats' => $this->getUserStats($userData['id'] ?? 0),
        'recent_projects' => $this->getRecentProjects($userData['id'] ?? 0),
        'pending_notifications' => $this->getPendingNotifications($userData['id'] ?? 0),
        'notification_count' => $this->getNotificationCount($userData['id'] ?? 0)
    ];

    return view('dashboard/user', $data);
}

    /**
     * Obtener estadísticas del usuario
     */
   
    /**
     * Página de información del sistema
     */
    public function about()
    {
        $session = session();
        
        $data = [
            'title' => 'Acerca del Sistema Multi-Área UC',
            'version' => '1.0.0',
            'description' => 'Sistema para gestión integral de proyectos de desarrollo con flujo de aprobaciones por múltiples áreas especializadas.',
            'features' => [
                'Autenticación vía CAS de la Universidad Católica',
                'Gestión de proyectos por etapas',
                'Flujo de aprobaciones multi-área',
                'Subida y gestión de documentos',
                'Notificaciones automáticas por email',
                'Auditoría completa de acciones',
                'Reportes y estadísticas',
                'Dashboards especializados por tipo de usuario'
            ],
            'user' => $session->has('user_authenticated') ? [
                'full_name' => $session->get('user_name'),
                'user_type' => $session->get('user_type')
            ] : null
        ];

        return view('home/about', $data);
    }

    /**
     * Página de ayuda
     */
    public function help()
    {
        $session = session();
        
        $data = [
            'title' => 'Ayuda - Sistema Multi-Área UC',
            'user' => $session->has('user_authenticated') ? [
                'full_name' => $session->get('user_name'),
                'user_type' => $session->get('user_type')
            ] : null,
            'faqs' => $this->getFAQs(),
            'contact_info' => [
                'email' => 'soporte-multiarea@uc.cl',
                'phone' => '+56 2 2354 4000',
                'hours' => 'Lunes a Viernes 8:00 - 18:00'
            ]
        ];

        return view('home/help', $data);
    }

    /**
     * Estado del sistema (health check)
     */
    public function status()
    {
        $status = [
            'system' => 'operational',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0',
            'environment' => ENVIRONMENT,
            'components' => []
        ];

        try {
            // Verificar base de datos
            $db = \Config\Database::connect();
            $status['components']['database'] = [
                'status' => $db->connID ? 'ok' : 'error',
                'response_time' => $this->measureDbResponseTime()
            ];

            // Verificar sistema de archivos
            $uploadsPath = WRITEPATH . '../public/uploads/';
            $status['components']['file_system'] = [
                'status' => is_writable($uploadsPath) ? 'ok' : 'error',
                'uploads_writable' => is_writable($uploadsPath),
                'logs_writable' => is_writable(WRITEPATH . 'logs/')
            ];

            // Estado general
            $hasErrors = false;
            foreach ($status['components'] as $component) {
                if ($component['status'] === 'error') {
                    $hasErrors = true;
                    break;
                }
            }
            
            $status['system'] = $hasErrors ? 'degraded' : 'operational';

        } catch (\Exception $e) {
            $status['system'] = 'error';
            $status['error'] = $e->getMessage();
        }

        return $this->response->setJSON($status);
    }

    /**
     * Información básica para API
     */
    public function info()
    {
        $info = [
            'name' => 'Sistema Multi-Área UC',
            'version' => '1.0.0',
            'description' => 'Sistema de gestión de proyectos con flujo de aprobaciones multi-área',
            'environment' => ENVIRONMENT,
            'codeigniter_version' => \CodeIgniter\CodeIgniter::CI_VERSION,
            'php_version' => PHP_VERSION,
            'timezone' => date_default_timezone_get(),
            'memory_usage' => [
                'current' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB',
                'peak' => round(memory_get_peak_usage() / 1024 / 1024, 2) . ' MB'
            ]
        ];

        return $this->response->setJSON($info);
    }

    /**
     * Obtener preguntas frecuentes
     */
    private function getFAQs(): array
    {
        return [
            [
                'question' => '¿Cómo crear un nuevo proyecto?',
                'answer' => 'Ve a la sección "Proyectos" y haz clic en "Crear Proyecto". Completa la ficha de formalización con todos los datos requeridos.'
            ],
            [
                'question' => '¿Cómo subir documentos a mi proyecto?',
                'answer' => 'En la vista de tu proyecto, selecciona la fase correspondiente y usa el botón "Subir Documento" para agregar los archivos requeridos.'
            ],
            [
                'question' => '¿Por qué mi proyecto está en estado "Rechazado"?',
                'answer' => 'Si tu proyecto fue rechazado, revisa los comentarios del área responsable en la sección de "Observaciones" y realiza las correcciones solicitadas.'
            ]
        ];
    }

    /**
     * Medir tiempo de respuesta de la base de datos
     */
    private function measureDbResponseTime(): float
    {
        $start = microtime(true);
        
        try {
            $db = \Config\Database::connect();
            $db->query('SELECT 1');
            return round((microtime(true) - $start) * 1000, 2);
        } catch (\Exception $e) {
            return -1;
        }
    }

    /**
     * Dashboard para Super Administradores (temporal)
     */
    public function superAdminDashboard()
{
    $session = session();
    
    if (!$session->has('user_authenticated') || $session->get('user_type') !== 'super_admin') {
        return redirect()->to('/auth/login');
    }

    $userData = [
        'id' => $session->get('user_id'),
        'email' => $session->get('user_email'),
        'full_name' => $session->get('user_name'),
        'user_type' => $session->get('user_type')
    ];

    $data = [
        'title' => 'Dashboard Super Admin - Sistema Multi-Área UC',
        'navbar_type' => 'super_admin',
        'user' => $userData,
        'stats' => $this->getSuperAdminStats(),
        'recent_activity' => $this->getRecentSystemActivity(),
        'system_health' => $this->getSystemHealth()
    ];

    return view('dashboard/super_admin', $data);
}

    /**
     * Dashboard para Administradores de Área (temporal)
     */
   public function adminDashboard()
{
    $session = session();
    
    if (!$session->has('user_authenticated') || !in_array($session->get('user_type'), ['admin', 'super_admin'])) {
        return redirect()->to('/auth/login');
    }

    $userData = [
        'id' => $session->get('user_id'),
        'email' => $session->get('user_email'),
        'full_name' => $session->get('user_name'),
        'user_type' => $session->get('user_type')
    ];

    // Obtener áreas asignadas al administrador
    $userModel = new \App\Models\UserModel();
    $userWithAreas = $userModel->getUserWithAreas($userData['id']);
    $userData['assigned_areas'] = $userWithAreas['assigned_areas'] ?? [];

    $data = [
        'title' => 'Dashboard Administrador - Sistema Multi-Área UC',
        'navbar_type' => 'admin',
        'user' => $userData,
        'stats' => $this->getAdminStats($userData['id']),
        'pending_projects' => $this->getPendingProjectsForAdmin($userData['id']),
        'overdue_projects' => $this->getOverdueProjectsForAdmin($userData['id'])
    ];

    return view('dashboard/admin', $data);
}
private function getUserStats(int $userId): array
{
    // Obtener estadísticas del usuario
    $projectModel = new \App\Models\ProjectModel();
    
    return [
        'total_projects' => $projectModel->where('requester_id', $userId)->countAllResults(),
        'active_projects' => $projectModel->where('requester_id', $userId)
                                         ->whereIn('status', ['submitted', 'in_progress'])
                                         ->countAllResults(),
        'completed_projects' => $projectModel->where('requester_id', $userId)
                                            ->where('status', 'completed')
                                            ->countAllResults(),
        'pending_projects' => $projectModel->where('requester_id', $userId)
                                          ->where('status', 'submitted')
                                          ->countAllResults()
    ];
}

private function getRecentProjects(int $userId, int $limit = 5): array
{
    $projectModel = new \App\Models\ProjectModel();
    return $projectModel->getProjectsByRequester($userId);
}

private function getPendingNotifications(int $userId, int $limit = 5): array
{
    $notificationModel = new \App\Models\NotificationModel();
    return $notificationModel->getUserNotifications($userId, false, $limit);
}

private function getNotificationCount(int $userId): int
{
    $notificationModel = new \App\Models\NotificationModel();
    return $notificationModel->getUnreadCount($userId);
}

private function getAdminStats(int $userId): array
{
    $areaAdminModel = new \App\Models\AreaAdminModel();
    $userAreas = $areaAdminModel->getUserAreas($userId);
    
    $stats = [
        'pending_reviews' => 0,
        'approved_projects' => 0,
        'rejected_projects' => 0,
        'overdue_projects' => 0,
        'monthly_approved' => 0,
        'monthly_rejected' => 0
    ];

    foreach ($userAreas as $area) {
        $phaseModel = new \App\Models\ProjectPhaseModel();
        $areaStats = $phaseModel->getPhaseStats($area['area_id']);
        
        $stats['pending_reviews'] += $areaStats['pending'] + $areaStats['assigned'];
        $stats['approved_projects'] += $areaStats['completed'];
        $stats['rejected_projects'] += $areaStats['rejected'];
    }

    return $stats;
}

private function getPendingProjectsForAdmin(int $userId): array
{
    $areaAdminModel = new \App\Models\AreaAdminModel();
    $userAreas = $areaAdminModel->getUserAreas($userId);
    
    $projects = [];
    foreach ($userAreas as $area) {
        $projectModel = new \App\Models\ProjectModel();
        $areaProjects = $projectModel->getProjectsByArea($area['area_id']);
        $projects = array_merge($projects, $areaProjects);
    }
    
    return $projects;
}

private function getSuperAdminStats(): array
{
    $userModel = new \App\Models\UserModel();
    $projectModel = new \App\Models\ProjectModel();
    $areaModel = new \App\Models\AreaModel();
    
    return [
        'total_users' => $userModel->countAll(),
        'total_admins' => $userModel->where('user_type', 'admin')->countAllResults(),
        'total_projects' => $projectModel->countAll(),
        'active_areas' => $areaModel->where('is_active', 1)->countAllResults(),
        'new_users_today' => $userModel->where('created_at >=', date('Y-m-d'))->countAllResults(),
        'completed_today' => $projectModel->where('actual_completion >=', date('Y-m-d'))->countAllResults(),
        'storage_usage' => $this->getStorageUsage(),
        'pending_approvals' => 0, // Implementar según necesidades
        'failed_jobs' => 0        // Implementar según necesidades
    ];
}

private function getRecentSystemActivity(): array
{
    $auditModel = new \App\Models\AuditLogModel();
    return $auditModel->searchLogs([
        'limit' => 20,
        'order_by' => 'created_at',
        'order_dir' => 'DESC'
    ]);
}

private function getSystemHealth(): array
{
    return [
        'status' => 'healthy',
        'uptime' => '99.9%',
        'last_check' => date('Y-m-d H:i:s')
    ];
}

private function getStorageUsage(): float
{
    // Calcular uso de almacenamiento
    $uploadPath = FCPATH . 'uploads/';
    if (is_dir($uploadPath)) {
        $size = $this->getDirSize($uploadPath);
        $maxSize = 10 * 1024 * 1024 * 1024; // 10GB límite ejemplo
        return ($size / $maxSize) * 100;
    }
    return 0;
}

private function getDirSize($directory): int
{
    $size = 0;
    if (is_dir($directory)) {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
            $size += $file->getSize();
        }
    }
    return $size;
}
}