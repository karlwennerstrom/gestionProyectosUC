<?php

namespace App\Controllers;

use App\Controllers\BaseController;

/**
 * Controlador de Administrador de Área - CORREGIDO
 * Sistema Multi-Área Universidad Católica
 */
class AdminController extends BaseController
{
    /**
     * Verificar permisos de administrador
     */
    private function checkAdminPermissions(): bool
    {
        $session = session();
        
        if (!$session->has('user_authenticated')) {
            return false;
        }

        $userType = $session->get('user_type');
        return in_array($userType, ['admin', 'super_admin']);
    }

    /**
     * Dashboard principal del administrador
     */
    public function dashboard()
    {
        try {
            log_message('debug', 'AdminController::dashboard() - Iniciando');
            
            // Verificar autenticación y permisos
            if (!$this->checkAdminPermissions()) {
                log_message('warning', 'Acceso denegado a admin dashboard');
                return redirect()->to('/auth/login')->with('error', 'Acceso denegado');
            }

            $session = session();
            
            $userData = [
                'id' => $session->get('user_id'),
                'email' => $session->get('user_email'),
                'full_name' => $session->get('user_name'),
                'user_type' => $session->get('user_type')
            ];

            log_message('debug', 'AdminController::dashboard() - Usuario: ' . json_encode($userData));

            // Obtener áreas asignadas (simulado por ahora)
            $userData['assigned_areas'] = $this->getSimulatedUserAreas($userData['id']);

            $data = [
                'title' => 'Dashboard Administrador - Sistema Multi-Área UC',
                'navbar_type' => 'admin',
                'user' => $userData,
                'stats' => $this->getSimulatedAdminStats(),
                'pending_projects' => $this->getSimulatedPendingProjects(),
                'overdue_projects' => []
            ];

            log_message('debug', 'AdminController::dashboard() - Datos preparados, mostrando vista');
            
            return view('dashboard/admin', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en AdminController::dashboard(): ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            
            // En caso de error, mostrar página de error personalizada
            return $this->response->setStatusCode(500)
                                 ->setBody('Error interno del servidor. Revisa los logs para más detalles.');
        }
    }

    /**
     * Obtener áreas simuladas para el usuario
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
     * Obtener estadísticas simuladas del administrador
     */
    private function getSimulatedAdminStats(): array
    {
        return [
            'pending_reviews' => 3,
            'approved_projects' => 12,
            'rejected_projects' => 2,
            'overdue_projects' => 1,
            'monthly_approved' => 8,
            'monthly_rejected' => 1
        ];
    }

    /**
     * Obtener proyectos pendientes simulados
     */
    private function getSimulatedPendingProjects(): array
    {
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
    }

    /**
     * Ver proyectos pendientes de revisión
     */
    public function pendingProjects()
    {
        try {
            if (!$this->checkAdminPermissions()) {
                return redirect()->to('/auth/login');
            }

            $session = session();
            
            $data = [
                'title' => 'Proyectos Pendientes - Admin',
                'navbar_type' => 'admin',
                'user' => [
                    'id' => $session->get('user_id'),
                    'full_name' => $session->get('user_name'),
                    'user_type' => $session->get('user_type')
                ],
                'pending_projects' => $this->getSimulatedPendingProjects()
            ];

            return view('admin/projects/pending', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en AdminController::pendingProjects(): ' . $e->getMessage());
            return redirect()->to('/admin/dashboard')->with('error', 'Error al cargar proyectos pendientes');
        }
    }

    /**
     * Ver proyecto específico para revisión
     */
    public function reviewProject($projectId)
    {
        try {
            if (!$this->checkAdminPermissions()) {
                return redirect()->to('/auth/login');
            }

            // Por ahora proyecto simulado
            $project = [
                'id' => $projectId,
                'code' => 'PROJ-2025-001',
                'title' => 'Sistema de Gestión Académica',
                'description' => 'Sistema para gestión de notas y cursos',
                'status' => 'in_progress',
                'current_area_id' => 1,
                'requester_name' => 'Juan Pérez',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $session = session();
            
            $data = [
                'title' => 'Revisar Proyecto - ' . $project['code'],
                'navbar_type' => 'admin',
                'user' => [
                    'id' => $session->get('user_id'),
                    'full_name' => $session->get('user_name'),
                    'user_type' => $session->get('user_type')
                ],
                'project' => $project
            ];

            return view('admin/projects/review', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en AdminController::reviewProject(): ' . $e->getMessage());
            return redirect()->to('/admin/dashboard')->with('error', 'Error al cargar proyecto para revisión');
        }
    }

    /**
     * API: Obtener estadísticas actualizadas
     */
    public function apiStats()
    {
        try {
            if (!$this->checkAdminPermissions()) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
            }

            $stats = $this->getSimulatedAdminStats();
            
            return $this->response->setJSON($stats);

        } catch (\Exception $e) {
            log_message('error', 'Error en AdminController::apiStats(): ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Internal server error']);
        }
    }

    /**
     * Gestión de documentos
     */
    public function documents()
    {
        try {
            if (!$this->checkAdminPermissions()) {
                return redirect()->to('/auth/login');
            }

            $session = session();
            
            $data = [
                'title' => 'Gestión de Documentos - Admin',
                'navbar_type' => 'admin',
                'user' => [
                    'id' => $session->get('user_id'),
                    'full_name' => $session->get('user_name'),
                    'user_type' => $session->get('user_type')
                ]
            ];

            return view('admin/documents/index', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en AdminController::documents(): ' . $e->getMessage());
            return redirect()->to('/admin/dashboard')->with('error', 'Error al cargar gestión de documentos');
        }
    }

    /**
     * Reportes del área
     */
    public function reports()
    {
        try {
            if (!$this->checkAdminPermissions()) {
                return redirect()->to('/auth/login');
            }

            $session = session();
            
            $data = [
                'title' => 'Reportes de Área - Admin',
                'navbar_type' => 'admin',
                'user' => [
                    'id' => $session->get('user_id'),
                    'full_name' => $session->get('user_name'),
                    'user_type' => $session->get('user_type')
                ]
            ];

            return view('admin/reports/index', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en AdminController::reports(): ' . $e->getMessage());
            return redirect()->to('/admin/dashboard')->with('error', 'Error al cargar reportes');
        }
    }

    /**
     * Configuración del área
     */
    public function settings()
    {
        try {
            if (!$this->checkAdminPermissions()) {
                return redirect()->to('/auth/login');
            }

            $session = session();
            
            $data = [
                'title' => 'Configuración - Admin',
                'navbar_type' => 'admin',
                'user' => [
                    'id' => $session->get('user_id'),
                    'full_name' => $session->get('user_name'),
                    'user_type' => $session->get('user_type')
                ]
            ];

            return view('admin/settings/index', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en AdminController::settings(): ' . $e->getMessage());
            return redirect()->to('/admin/dashboard')->with('error', 'Error al cargar configuración');
        }
    }
}