<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProjectModel;
use App\Models\DocumentModel;
use App\Models\NotificationModel;

/**
 * Controlador de Proyectos para Usuarios
 * Sistema Multi-Área Universidad Católica
 */
class ProjectController extends BaseController
{
    protected ProjectModel $projectModel;
    protected DocumentModel $documentModel;
    protected NotificationModel $notificationModel;

    public function __construct()
    {
        $this->projectModel = new ProjectModel();
        $this->documentModel = new DocumentModel();
        $this->notificationModel = new NotificationModel();
    }

    /**
     * Lista de proyectos del usuario
     */
    public function index()
    {
        $session = session();
        
        if (!$session->has('user_authenticated')) {
            return redirect()->to('/auth/login');
        }

        $userId = $session->get('user_id');
        
        try {
            // Obtener filtros de la URL
            $filters = [
                'search' => $this->request->getGet('search'),
                'status' => $this->request->getGet('status'),
                'priority' => $this->request->getGet('priority'),
                'order_by' => $this->request->getGet('order_by') ?? 'created_at',
                'order_dir' => $this->request->getGet('order_dir') ?? 'DESC'
            ];

            // Obtener proyectos del usuario
            $projects = $this->getSimulatedUserProjects($userId, $filters);
            
            $data = [
                'title' => 'Mis Proyectos - Sistema Multi-Área UC',
                'navbar_type' => 'dashboard',
                'user' => [
                    'id' => $session->get('user_id'),
                    'full_name' => $session->get('user_name'),
                    'user_type' => $session->get('user_type')
                ],
                'projects' => $projects,
                'stats' => $this->getProjectStats($projects),
                'filters' => $filters
            ];

            return view('projects/index', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en ProjectController::index(): ' . $e->getMessage());
            return redirect()->to('/dashboard')->with('error', 'Error al cargar proyectos');
        }
    }

    /**
     * Formulario para crear nuevo proyecto
     */
    public function create()
    {
        $session = session();
        
        if (!$session->has('user_authenticated')) {
            return redirect()->to('/auth/login');
        }

        $data = [
            'title' => 'Crear Proyecto - Sistema Multi-Área UC',
            'navbar_type' => 'dashboard',
            'user' => [
                'id' => $session->get('user_id'),
                'full_name' => $session->get('user_name'),
                'user_type' => $session->get('user_type')
            ],
            'project_types' => $this->getProjectTypes(),
            'departments' => $this->getDepartments(),
            'priorities' => $this->getPriorities()
        ];

        return view('projects/create', $data);
    }

    /**
     * Procesar creación de proyecto
     */
    public function store()
    {
        $session = session();
        
        if (!$session->has('user_authenticated')) {
            return redirect()->to('/auth/login');
        }

        try {
            // Validar datos del formulario
            $validationRules = [
                'title' => 'required|min_length[5]|max_length[255]',
                'description' => 'required|min_length[20]',
                'department' => 'required',
                'priority' => 'required|in_list[low,medium,high,critical]',
                'contact_email' => 'required|valid_email',
                'contact_phone' => 'permit_empty|min_length[8]',
                'estimated_completion' => 'permit_empty|valid_date',
                'budget' => 'permit_empty|decimal'
            ];

            if (!$this->validate($validationRules)) {
                return redirect()->back()
                               ->withInput()
                               ->with('validation', $this->validator);
            }

            $userId = $session->get('user_id');
            
            // Datos del proyecto
            $projectData = [
                'title' => $this->request->getPost('title'),
                'description' => $this->request->getPost('description'),
                'requester_id' => $userId,
                'department' => $this->request->getPost('department'),
                'priority' => $this->request->getPost('priority'),
                'contact_email' => $this->request->getPost('contact_email'),
                'contact_phone' => $this->request->getPost('contact_phone'),
                'estimated_completion' => $this->request->getPost('estimated_completion'),
                'budget' => $this->request->getPost('budget'),
                'status' => 'draft',
                'additional_info' => json_encode([
                    'project_type' => $this->request->getPost('project_type'),
                    'special_requirements' => $this->request->getPost('special_requirements')
                ])
            ];

            // Simular creación (en la implementación real usarías $this->projectModel->insert())
            $projectId = $this->simulateProjectCreation($projectData);

            if ($projectId) {
                // Crear notificación
                $this->notificationModel->createNotification([
                    'user_id' => $userId,
                    'project_id' => $projectId,
                    'type' => 'project_created',
                    'title' => 'Proyecto creado exitosamente',
                    'message' => "Tu proyecto '{$projectData['title']}' ha sido creado como borrador.",
                    'priority' => 'normal'
                ]);

                return redirect()->to("/projects/{$projectId}")
                               ->with('success', 'Proyecto creado exitosamente');
            } else {
                return redirect()->back()
                               ->withInput()
                               ->with('error', 'Error al crear el proyecto');
            }

        } catch (\Exception $e) {
            log_message('error', 'Error en ProjectController::store(): ' . $e->getMessage());
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Error interno del sistema');
        }
    }

    /**
     * Ver detalles de un proyecto
     */
    public function show($projectId)
    {
        $session = session();
        
        if (!$session->has('user_authenticated')) {
            return redirect()->to('/auth/login');
        }

        try {
            $project = $this->getSimulatedProject($projectId);
            
            if (!$project) {
                return redirect()->to('/projects')->with('error', 'Proyecto no encontrado');
            }

            // Verificar que el proyecto pertenece al usuario
            if ($project['requester_id'] != $session->get('user_id')) {
                return redirect()->to('/projects')->with('error', 'No tienes acceso a este proyecto');
            }

            $data = [
                'title' => "Proyecto {$project['code']} - Sistema Multi-Área UC",
                'navbar_type' => 'dashboard',
                'user' => [
                    'id' => $session->get('user_id'),
                    'full_name' => $session->get('user_name'),
                    'user_type' => $session->get('user_type')
                ],
                'project' => $project,
                'documents' => $this->getSimulatedProjectDocuments($projectId),
                'phases' => $this->getSimulatedProjectPhases($projectId),
                'activity_log' => $this->getSimulatedActivityLog($projectId)
            ];

            return view('projects/show', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en ProjectController::show(): ' . $e->getMessage());
            return redirect()->to('/projects')->with('error', 'Error al cargar el proyecto');
        }
    }

    /**
     * Formulario para editar proyecto (solo borradores y rechazados)
     */
    public function edit($projectId)
    {
        $session = session();
        
        if (!$session->has('user_authenticated')) {
            return redirect()->to('/auth/login');
        }

        try {
            $project = $this->getSimulatedProject($projectId);
            
            if (!$project || $project['requester_id'] != $session->get('user_id')) {
                return redirect()->to('/projects')->with('error', 'Proyecto no encontrado');
            }

            // Solo se pueden editar borradores y proyectos rechazados
            if (!in_array($project['status'], ['draft', 'rejected'])) {
                return redirect()->to("/projects/{$projectId}")
                               ->with('error', 'Este proyecto no se puede editar en su estado actual');
            }

            $data = [
                'title' => "Editar Proyecto {$project['code']} - Sistema Multi-Área UC",
                'navbar_type' => 'dashboard',
                'user' => [
                    'id' => $session->get('user_id'),
                    'full_name' => $session->get('user_name'),
                    'user_type' => $session->get('user_type')
                ],
                'project' => $project,
                'project_types' => $this->getProjectTypes(),
                'departments' => $this->getDepartments(),
                'priorities' => $this->getPriorities()
            ];

            return view('projects/edit', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en ProjectController::edit(): ' . $e->getMessage());
            return redirect()->to('/projects')->with('error', 'Error al cargar el proyecto para edición');
        }
    }

    /**
     * Subir documentos a un proyecto
     */
    public function uploadDocument($projectId)
    {
        $session = session();
        
        if (!$session->has('user_authenticated')) {
            return redirect()->to('/auth/login');
        }

        try {
            $project = $this->getSimulatedProject($projectId);
            
            if (!$project || $project['requester_id'] != $session->get('user_id')) {
                return redirect()->to('/projects')->with('error', 'Proyecto no encontrado');
            }

            $data = [
                'title' => "Subir Documentos - {$project['code']}",
                'navbar_type' => 'dashboard',
                'user' => [
                    'id' => $session->get('user_id'),
                    'full_name' => $session->get('user_name'),
                    'user_type' => $session->get('user_type')
                ],
                'project' => $project,
                'document_types' => $this->getDocumentTypes(),
                'current_documents' => $this->getSimulatedProjectDocuments($projectId)
            ];

            return view('projects/upload_document', $data);

        } catch (\Exception $e) {
            log_message('error', 'Error en ProjectController::uploadDocument(): ' . $e->getMessage());
            return redirect()->to("/projects/{$projectId}")->with('error', 'Error al cargar página de documentos');
        }
    }

    // =====================================================
    // MÉTODOS AUXILIARES Y SIMULACIONES
    // =====================================================

    private function getSimulatedUserProjects($userId, $filters = [])
    {
        $projects = [
            [
                'id' => 1,
                'code' => 'PROJ-2025-001',
                'title' => 'Sistema de Gestión Académica',
                'description' => 'Desarrollo de un sistema integral para la gestión de notas, cursos y estudiantes.',
                'status' => 'in_progress',
                'priority' => 'high',
                'completion_percentage' => 65.0,
                'current_area_name' => 'Arquitectura',
                'current_area_color' => '#10B981',
                'requester_id' => $userId,
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                'estimated_completion' => date('Y-m-d', strtotime('+15 days'))
            ],
            [
                'id' => 2,
                'code' => 'PROJ-2025-002',
                'title' => 'Portal de Estudiantes',
                'description' => 'Portal web para que estudiantes accedan a sus notas y horarios.',
                'status' => 'submitted',
                'priority' => 'medium',
                'completion_percentage' => 15.0,
                'current_area_name' => 'Formalización',
                'current_area_color' => '#3B82F6',
                'requester_id' => $userId,
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'estimated_completion' => date('Y-m-d', strtotime('+30 days'))
            ],
            [
                'id' => 3,
                'code' => 'PROJ-2025-003',
                'title' => 'API de Integración',
                'description' => 'API REST para integración con sistemas externos.',
                'status' => 'draft',
                'priority' => 'low',
                'completion_percentage' => 0.0,
                'current_area_name' => null,
                'current_area_color' => null,
                'requester_id' => $userId,
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'estimated_completion' => date('Y-m-d', strtotime('+45 days'))
            ]
        ];

        // Aplicar filtros
        if (!empty($filters['search'])) {
            $search = strtolower($filters['search']);
            $projects = array_filter($projects, function($project) use ($search) {
                return strpos(strtolower($project['title']), $search) !== false ||
                       strpos(strtolower($project['code']), $search) !== false ||
                       strpos(strtolower($project['description']), $search) !== false;
            });
        }

        if (!empty($filters['status'])) {
            $projects = array_filter($projects, function($project) use ($filters) {
                return $project['status'] === $filters['status'];
            });
        }

        if (!empty($filters['priority'])) {
            $projects = array_filter($projects, function($project) use ($filters) {
                return $project['priority'] === $filters['priority'];
            });
        }

        return $projects;
    }

    private function getSimulatedProject($projectId)
    {
        $projects = [
            1 => [
                'id' => 1,
                'code' => 'PROJ-2025-001',
                'title' => 'Sistema de Gestión Académica',
                'description' => 'Desarrollo de un sistema integral para la gestión de notas, cursos y estudiantes de la universidad. El sistema debe permitir a los profesores ingresar notas, a los estudiantes consultar sus calificaciones, y a los administradores generar reportes académicos.',
                'status' => 'in_progress',
                'priority' => 'high',
                'completion_percentage' => 65.0,
                'current_area_id' => 2,
                'current_area_name' => 'Arquitectura',
                'current_area_color' => '#10B981',
                'requester_id' => 1,
                'department' => 'Facultad de Ingeniería',
                'contact_email' => 'juan.perez@uc.cl',
                'contact_phone' => '+56 9 1234 5678',
                'budget' => 5000000,
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'estimated_completion' => date('Y-m-d', strtotime('+15 days')),
                'actual_completion' => null,
                'additional_info' => json_encode([
                    'project_type' => 'sistema_web',
                    'special_requirements' => 'Integración con sistema legacy de notas'
                ])
            ]
        ];

        return $projects[$projectId] ?? null;
    }

    private function getSimulatedProjectDocuments($projectId)
    {
        return [
            [
                'id' => 1,
                'project_id' => $projectId,
                'document_type' => 'ficha_formalizacion',
                'original_name' => 'Ficha_Formalizacion.pdf',
                'file_size' => 2048000,
                'status' => 'approved',
                'version' => 1,
                'is_latest' => true,
                'uploaded_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'reviewed_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'reviewer_comments' => 'Documento completo y bien estructurado'
            ],
            [
                'id' => 2,
                'project_id' => $projectId,
                'document_type' => 'especificacion_tecnica',
                'original_name' => 'Especificacion_Tecnica_v2.docx',
                'file_size' => 1536000,
                'status' => 'pending',
                'version' => 2,
                'is_latest' => true,
                'uploaded_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'reviewed_at' => null,
                'reviewer_comments' => null
            ]
        ];
    }

    private function getSimulatedProjectPhases($projectId)
    {
        return [
            [
                'id' => 1,
                'area_name' => 'Formalización',
                'area_color' => '#3B82F6',
                'status' => 'completed',
                'phase_order' => 1,
                'started_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
                'completed_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'estimated_hours' => 24,
                'actual_hours' => 20,
                'assigned_to_name' => 'Carlos Mendoza'
            ],
            [
                'id' => 2,
                'area_name' => 'Arquitectura',
                'area_color' => '#10B981',
                'status' => 'in_progress',
                'phase_order' => 2,
                'started_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'completed_at' => null,
                'estimated_hours' => 56,
                'actual_hours' => null,
                'assigned_to_name' => 'Ana Silva'
            ],
            [
                'id' => 3,
                'area_name' => 'Infraestructura',
                'area_color' => '#F59E0B',
                'status' => 'pending',
                'phase_order' => 3,
                'started_at' => null,
                'completed_at' => null,
                'estimated_hours' => 40,
                'actual_hours' => null,
                'assigned_to_name' => null
            ]
        ];
    }

    private function getSimulatedActivityLog($projectId)
    {
        return [
            [
                'id' => 1,
                'action' => 'project_created',
                'description' => 'Proyecto creado',
                'user_name' => 'Juan Pérez',
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 days'))
            ],
            [
                'id' => 2,
                'action' => 'document_uploaded',
                'description' => 'Documento subido: Ficha_Formalizacion.pdf',
                'user_name' => 'Juan Pérez',
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 days'))
            ],
            [
                'id' => 3,
                'action' => 'phase_completed',
                'description' => 'Fase completada: Formalización',
                'user_name' => 'Carlos Mendoza',
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 days'))
            ],
            [
                'id' => 4,
                'action' => 'phase_started',
                'description' => 'Fase iniciada: Arquitectura',
                'user_name' => 'Ana Silva',
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
            ]
        ];
    }

    private function getProjectStats($projects)
    {
        $total = count($projects);
        $active = count(array_filter($projects, fn($p) => in_array($p['status'], ['submitted', 'in_progress'])));
        $completed = count(array_filter($projects, fn($p) => $p['status'] === 'completed'));
        $pending = count(array_filter($projects, fn($p) => $p['status'] === 'draft'));

        return [
            'total' => $total,
            'active' => $active,
            'completed' => $completed,
            'pending' => $pending
        ];
    }

    private function simulateProjectCreation($projectData)
    {
        // En la implementación real, aquí usarías:
        // return $this->projectModel->insert($projectData);
        
        // Simular ID generado
        return rand(100, 999);
    }

    private function getProjectTypes()
    {
        return [
            'sistema_web' => 'Sistema Web',
            'aplicacion_movil' => 'Aplicación Móvil',
            'api_rest' => 'API REST',
            'base_datos' => 'Base de Datos',
            'integracion' => 'Integración de Sistemas',
            'migracion' => 'Migración de Datos',
            'otro' => 'Otro'
        ];
    }

    private function getDepartments()
    {
        return [
            'Facultad de Ingeniería',
            'Facultad de Medicina',
            'Facultad de Derecho',
            'Facultad de Ciencias Económicas',
            'Facultad de Educación',
            'Facultad de Arquitectura',
            'Vicerrectoría Académica',
            'Vicerrectoría de Investigación',
            'Dirección de Tecnologías',
            'Registro Académico',
            'Bibliotecas',
            'Otro'
        ];
    }

    private function getPriorities()
    {
        return [
            'low' => 'Baja',
            'medium' => 'Media',
            'high' => 'Alta',
            'critical' => 'Crítica'
        ];
    }

    private function getDocumentTypes()
    {
        return [
            'ficha_formalizacion' => 'Ficha de Formalización',
            'especificacion_tecnica' => 'Especificación Técnica',
            'diagrama_arquitectura' => 'Diagrama de Arquitectura',
            'manual_usuario' => 'Manual de Usuario',
            'documentacion_tecnica' => 'Documentación Técnica',
            'plan_pruebas' => 'Plan de Pruebas',
            'certificado_seguridad' => 'Certificado de Seguridad',
            'otros' => 'Otros'
        ];
    }
}