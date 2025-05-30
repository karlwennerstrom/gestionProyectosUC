<?php
/* ===================================================================
   SOLUCIÓN PARA ERROR DE RUTA /admin/dashboard
   ================================================================= */

// 1. CREAR: app/Controllers/AdminController.php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\ProjectModel;
use App\Models\AreaAdminModel;
use App\Models\ProjectPhaseModel;
use App\Models\NotificationModel;

/**
 * Controlador de Administrador de Área
 * Sistema Multi-Área Universidad Católica
 */
class AdminController extends BaseController
{
    protected UserModel $userModel;
    protected ProjectModel $projectModel;
    protected AreaAdminModel $areaAdminModel;
    protected ProjectPhaseModel $phaseModel;
    protected NotificationModel $notificationModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->projectModel = new ProjectModel();
        $this->areaAdminModel = new AreaAdminModel();
        $this->phaseModel = new ProjectPhaseModel();
        $this->notificationModel = new NotificationModel();
    }

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
        // Verificar autenticación y permisos
        if (!$this->checkAdminPermissions()) {
            return redirect()->to('/auth/login')->with('error', 'Acceso denegado');
        }

        $session = session();
        
        $userData = [
            'id' => $session->get('user_id'),
            'email' => $session->get('user_email'),
            'full_name' => $session->get('user_name'),
            'user_type' => $session->get('user_type')
        ];

        // Obtener áreas asignadas al administrador
        $userWithAreas = $this->userModel->getUserWithAreas($userData['id']);
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

    /**
     * Obtener estadísticas del administrador
     */
    private function getAdminStats(int $userId): array
    {
        $userAreas = $this->areaAdminModel->getUserAreas($userId);
        
        $stats = [
            'pending_reviews' => 0,
            'approved_projects' => 0,
            'rejected_projects' => 0,
            'overdue_projects' => 0,
            'monthly_approved' => 0,
            'monthly_rejected' => 0
        ];

        foreach ($userAreas as $area) {
            $areaStats = $this->phaseModel->getPhaseStats($area['area_id']);
            
            $stats['pending_reviews'] += $areaStats['pending'] + $areaStats['assigned'];
            $stats['approved_projects'] += $areaStats['completed'];
            $stats['rejected_projects'] += $areaStats['rejected'];
            $stats['overdue_projects'] += $this->phaseModel->getOverdueCount($area['area_id']);
        }

        // Estadísticas del mes actual
        $firstDayOfMonth = date('Y-m-01');
        foreach ($userAreas as $area) {
            $monthlyCompleted = $this->phaseModel->where('area_id', $area['area_id'])
                                                 ->where('status', 'completed')
                                                 ->where('completed_at >=', $firstDayOfMonth)
                                                 ->countAllResults();
            
            $monthlyRejected = $this->phaseModel->where('area_id', $area['area_id'])
                                                ->where('status', 'rejected')
                                                ->where('completed_at >=', $firstDayOfMonth)
                                                ->countAllResults();
            
            $stats['monthly_approved'] += $monthlyCompleted;
            $stats['monthly_rejected'] += $monthlyRejected;
        }

        return $stats;
    }

    /**
     * Obtener proyectos pendientes para el administrador
     */
    private function getPendingProjectsForAdmin(int $userId): array
    {
        $userAreas = $this->areaAdminModel->getUserAreas($userId);
        
        $projects = [];
        foreach ($userAreas as $area) {
            $areaProjects = $this->projectModel->getProjectsByArea($area['area_id']);
            $projects = array_merge($projects, $areaProjects);
        }
        
        // Filtrar solo proyectos pendientes/en progreso
        return array_filter($projects, function($project) {
            return in_array($project['status'], ['submitted', 'in_progress']);
        });
    }

    /**
     * Obtener proyectos vencidos para el administrador
     */
    private function getOverdueProjectsForAdmin(int $userId): array
    {
        $userAreas = $this->areaAdminModel->getUserAreas($userId);
        
        $overdueProjects = [];
        foreach ($userAreas as $area) {
            $areaOverdue = $this->phaseModel->getOverduePhases();
            $overdueProjects = array_merge($overdueProjects, $areaOverdue);
        }
        
        return $overdueProjects;
    }

    /**
     * Ver proyectos pendientes de revisión
     */
    public function pendingProjects()
    {
        if (!$this->checkAdminPermissions()) {
            return redirect()->to('/auth/login');
        }

        $session = session();
        $userId = $session->get('user_id');
        
        $data = [
            'title' => 'Proyectos Pendientes - Admin',
            'navbar_type' => 'admin',
            'user' => [
                'id' => $userId,
                'full_name' => $session->get('user_name'),
                'user_type' => $session->get('user_type')
            ],
            'pending_projects' => $this->getPendingProjectsForAdmin($userId)
        ];

        return view('admin/projects/pending', $data);
    }

    /**
     * Ver proyecto específico para revisión
     */
    public function reviewProject($projectId)
    {
        if (!$this->checkAdminPermissions()) {
            return redirect()->to('/auth/login');
        }

        $project = $this->projectModel->getProjectWithDetails($projectId);
        
        if (!$project) {
            return redirect()->to('/admin/dashboard')->with('error', 'Proyecto no encontrado');
        }

        // Verificar que el admin tiene permisos sobre este proyecto
        $session = session();
        $userId = $session->get('user_id');
        $userAreas = $this->areaAdminModel->getUserAreas($userId);
        
        $hasPermission = false;
        foreach ($userAreas as $area) {
            if ($area['area_id'] == $project['current_area_id']) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission && $session->get('user_type') !== 'super_admin') {
            return redirect()->to('/admin/dashboard')->with('error', 'No tienes permisos para revisar este proyecto');
        }

        $data = [
            'title' => 'Revisar Proyecto - ' . $project['code'],
            'navbar_type' => 'admin',
            'user' => [
                'id' => $userId,
                'full_name' => $session->get('user_name'),
                'user_type' => $session->get('user_type')
            ],
            'project' => $project
        ];

        return view('admin/projects/review', $data);
    }

    /**
     * API: Obtener estadísticas actualizadas
     */
    public function apiStats()
    {
        if (!$this->checkAdminPermissions()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
        }

        $session = session();
        $userId = $session->get('user_id');
        
        $stats = $this->getAdminStats($userId);
        
        return $this->response->setJSON($stats);
    }

    /**
     * API: Exportar reporte del área
     */
    public function exportReport()
    {
        if (!$this->checkAdminPermissions()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
        }

        // Implementar exportación de reporte
        // Por ahora retornar mock data
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Reporte generado exitosamente',
            'filename' => 'reporte_area_' . date('Y-m-d') . '.xlsx'
        ]);
    }

    /**
     * Gestión de documentos
     */
    public function documents()
    {
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
    }

    /**
     * Reportes del área
     */
    public function reports()
    {
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
    }

    /**
     * Configuración del área
     */
    public function settings()
    {
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
    }
}
