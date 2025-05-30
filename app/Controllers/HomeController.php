<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

/**
 * Controlador Principal del Sistema - CORREGIDO
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
     * Dashboard para Super Administradores
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
     * Dashboard para Administradores de Área
     */
    public function adminDashboard()
    {
        try {
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

            // Obtener áreas asignadas (simuladas por ahora)
            $userData['assigned_areas'] = $this->getSimulatedUserAreas($userData['id']);

            $data = [
                'title' => 'Dashboard Administrador - Sistema Multi-Área UC',
                'navbar_type' => 'admin',
                'user' => $userData,
                'stats' => $this->getAdminStats($userData['id']),
                'pending_projects' => $this->getPendingProjectsForAdmin($userData['id']),
                'overdue_projects' => $this->getOverdueProjectsForAdmin($userData['id'])
            ];

            return view('dashboard/admin', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en adminDashboard: ' . $e->getMessage());
            return redirect()->to('/dashboard')->with('error', 'Error al cargar dashboard de administrador');
        }
    }

    // =====================================================
    // MÉTODOS PRIVADOS PARA ESTADÍSTICAS Y DATOS
    // =====================================================

    /**
     * Obtener estadísticas del usuario
     */
    private function getUserStats(int $userId): array
    {
        try {
            // Simulado por ahora - reemplazar con datos reales cuando esté la BD
            return [
                'total_projects' => 3,
                'active_projects' => 2,
                'completed_projects' => 1,
                'pending_projects' => 1
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error obteniendo stats de usuario: ' . $e->getMessage());
            return [
                'total_projects' => 0,
                'active_projects' => 0,
                'completed_projects' => 0,
                'pending_projects' => 0
            ];
        }
    }

    /**
     * Obtener proyectos recientes del usuario
     */
    private function getRecentProjects(int $userId, int $limit = 5): array
    {
        try {
            // Simulado por ahora
            return [
                [
                    'id' => 1,
                    'code' => 'PROJ-2025-001',
                    'title' => 'Sistema de Gestión Académica',
                    'status' => 'in_progress',
                    'completion_percentage' => 65,
                    'created_at' => date('Y-m-d H:i:s', strtotime('-5 days'))
                ],
                [
                    'id' => 2,
                    'code' => 'PROJ-2025-002',
                    'title' => 'Portal de Estudiantes',
                    'status' => 'submitted',
                    'completion_percentage' => 15,
                    'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error obteniendo proyectos recientes: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener notificaciones pendientes
     */
    private function getPendingNotifications(int $userId, int $limit = 5): array
    {
        try {
            // Simulado por ahora
            return [
                [
                    'id' => 1,
                    'type' => 'project_updated',
                    'title' => 'Proyecto actualizado',
                    'message' => 'Tu proyecto PROJ-2025-001 ha avanzado a la fase de Arquitectura',
                    'read_status' => false,
                    'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour'))
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error obteniendo notificaciones: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener conteo de notificaciones no leídas
     */
    private function getNotificationCount(int $userId): int
    {
        try {
            // Simulado por ahora
            return 2;
        } catch (\Exception $e) {
            log_message('error', 'Error obteniendo conteo de notificaciones: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtener estadísticas de administrador
     */
    private function getAdminStats(int $userId): array
    {
        try {
            // Simulado por ahora
            return [
                'pending_reviews' => 4,
                'approved_projects' => 15,
                'rejected_projects' => 2,
                'overdue_projects' => 1,
                'monthly_approved' => 10,
                'monthly_rejected' => 1
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error obteniendo stats de admin: ' . $e->getMessage());
            return [
                'pending_reviews' => 0,
                'approved_projects' => 0,
                'rejected_projects' => 0,
                'overdue_projects' => 0,
                'monthly_approved' => 0,
                'monthly_rejected' => 0
            ];
        }
    }

    /**
     * Obtener proyectos pendientes para administrador
     */
    private function getPendingProjectsForAdmin(int $userId): array
    {
        try {
            // Simulado por ahora
            return [
                [
                    'id' => 1,
                    'code' => 'PROJ-2025-001',
                    'title' => 'Sistema de Gestión Académica',
                    'priority' => 'high',
                    'requester_name' => 'Juan Pérez',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                    'due_date' => date('Y-m-d', strtotime('+5 days'))
                ],
                [
                    'id' => 2,
                    'code' => 'PROJ-2025-002',
                    'title' => 'Portal de Estudiantes',
                    'priority' => 'medium',
                    'requester_name' => 'María González',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                    'due_date' => date('Y-m-d', strtotime('+7 days'))
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error obteniendo proyectos pendientes para admin: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener proyectos vencidos para administrador
     */
    private function getOverdueProjectsForAdmin(int $userId): array
    {
        try {
            // Simulado por ahora
            return [
                [
                    'id' => 3,
                    'code' => 'PROJ-2025-003',
                    'title' => 'Sistema de Biblioteca',
                    'priority' => 'medium',
                    'requester_name' => 'Carlos Silva',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
                    'due_date' => date('Y-m-d', strtotime('-2 days')),
                    'days_overdue' => 2
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error obteniendo proyectos vencidos para admin: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener áreas simuladas para usuario
     */
    private function getSimulatedUserAreas(int $userId): array
    {
        return [
            [
                'area_id' => 1,
                'area_name' => 'Arquitectura',
                'area_color' => '#10B981',
                'role' => 'admin'
            ],
            [
                'area_id' => 2,
                'area_name' => 'Seguridad',
                'area_color' => '#EF4444',
                'role' => 'reviewer'
            ]
        ];
    }

    /**
     * Obtener estadísticas de super administrador
     */
    private function getSuperAdminStats(): array
    {
        try {
            return [
                'total_users' => 25,
                'total_admins' => 8,
                'total_projects' => 45,
                'active_areas' => 9,
                'new_users_today' => 2,
                'completed_today' => 3,
                'storage_usage' => 45,
                'pending_approvals' => 0,
                'failed_jobs' => 0
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error obteniendo stats de super admin: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener actividad reciente del sistema
     */
    private function getRecentSystemActivity(): array
    {
        try {
            return [
                [
                    'type' => 'user_login',
                    'description' => 'Carlos Mendoza inició sesión',
                    'user_name' => 'Carlos Mendoza',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-15 minutes'))
                ],
                [
                    'type' => 'project_created',
                    'description' => 'Nuevo proyecto PROJ-2025-004 creado',
                    'user_name' => 'Ana Silva',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour'))
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error obteniendo actividad del sistema: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener estado de salud del sistema
     */
    private function getSystemHealth(): array
    {
        return [
            'status' => 'healthy',
            'uptime' => '99.9%',
            'last_check' => date('Y-m-d H:i:s')
        ];
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
     * Obtener uso de almacenamiento
     */
    private function getStorageUsage(): float
    {
        try {
            $uploadPath = FCPATH . 'uploads/';
            if (is_dir($uploadPath)) {
                $size = $this->getDirSize($uploadPath);
                $maxSize = 10 * 1024 * 1024 * 1024; // 10GB límite ejemplo
                return ($size / $maxSize) * 100;
            }
            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Obtener tamaño de directorio
     */
    private function getDirSize($directory): int
    {
        $size = 0;
        try {
            if (is_dir($directory)) {
                foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)) as $file) {
                    $size += $file->getSize();
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'Error calculando tamaño de directorio: ' . $e->getMessage());
        }
        return $size;
    }
}