<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo de Proyectos
 * Sistema Multi-Área Universidad Católica
 */
class ProjectModel extends Model
{
    protected $table = 'projects';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'code',
        'title',
        'description',
        'requester_id',
        'current_area_id',
        'priority',
        'status',
        'completion_percentage',
        'estimated_completion',
        'actual_completion',
        'budget',
        'department',
        'contact_email',
        'contact_phone',
        'additional_info'
    ];

    // Fechas
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = null;

    // Validación
    protected $validationRules = [
        'code' => 'required|min_length[8]|max_length[20]|is_unique[projects.code,id,{id}]',
        'title' => 'required|min_length[5]|max_length[255]',
        'description' => 'required|min_length[20]',
        'requester_id' => 'required|integer|is_not_unique[users.id]',
        'priority' => 'required|in_list[low,medium,high,critical]',
        'status' => 'required|in_list[draft,submitted,in_progress,on_hold,completed,cancelled,rejected]',
        'completion_percentage' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
        'budget' => 'permit_empty|decimal|greater_than[0]',
        'contact_email' => 'required|valid_email',
        'contact_phone' => 'permit_empty|min_length[8]|max_length[20]'
    ];

    protected $validationMessages = [
        'code' => [
            'required' => 'El código del proyecto es obligatorio',
            'is_unique' => 'Ya existe un proyecto con este código'
        ],
        'title' => [
            'required' => 'El título del proyecto es obligatorio',
            'min_length' => 'El título debe tener al menos 5 caracteres'
        ],
        'description' => [
            'required' => 'La descripción del proyecto es obligatoria',
            'min_length' => 'La descripción debe tener al menos 20 caracteres'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['generateProjectCode'];
    protected $beforeUpdate = ['updateCompletionStatus'];

    /**
     * Generar código único para el proyecto
     */
    protected function generateProjectCode(array $data): array
    {
        if (empty($data['data']['code'])) {
            $year = date('Y');
            $lastProject = $this->like('code', "PROJ-{$year}-", 'after')
                               ->orderBy('id', 'DESC')
                               ->first();

            if ($lastProject) {
                preg_match('/PROJ-\d{4}-(\d{3})/', $lastProject['code'], $matches);
                $nextNumber = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
            } else {
                $nextNumber = 1;
            }

            $data['data']['code'] = sprintf('PROJ-%s-%03d', $year, $nextNumber);
        }

        return $data;
    }

    /**
     * Actualizar estado basado en porcentaje de completación
     */
    protected function updateCompletionStatus(array $data): array
    {
        if (isset($data['data']['completion_percentage'])) {
            $percentage = $data['data']['completion_percentage'];
            
            if ($percentage >= 100) {
                $data['data']['status'] = 'completed';
                $data['data']['actual_completion'] = date('Y-m-d H:i:s');
            } elseif ($percentage > 0 && $data['data']['status'] === 'submitted') {
                $data['data']['status'] = 'in_progress';
            }
        }

        return $data;
    }

    /**
     * Obtener proyectos por usuario solicitante
     */
    public function getProjectsByRequester(int $requesterId): array
    {
        return $this->where('requester_id', $requesterId)
                   ->orderBy('created_at', 'DESC')
                   ->findAll();
    }

    /**
     * Obtener proyectos por área actual
     */
    public function getProjectsByArea(int $areaId): array
    {
        return $this->select('projects.*, users.full_name as requester_name, users.email as requester_email')
                   ->join('users', 'users.id = projects.requester_id')
                   ->where('projects.current_area_id', $areaId)
                   ->orderBy('projects.priority', 'DESC')
                   ->orderBy('projects.created_at', 'ASC')
                   ->findAll();
    }

    /**
     * Obtener proyectos por estado
     */
    public function getProjectsByStatus(string $status): array
    {
        return $this->select('projects.*, users.full_name as requester_name')
                   ->join('users', 'users.id = projects.requester_id')
                   ->where('projects.status', $status)
                   ->orderBy('projects.created_at', 'DESC')
                   ->findAll();
    }

    /**
     * Buscar proyectos con filtros
     */
    public function searchProjects(array $filters = []): array
    {
        $builder = $this->select('projects.*, users.full_name as requester_name, areas.name as current_area_name')
                       ->join('users', 'users.id = projects.requester_id')
                       ->join('areas', 'areas.id = projects.current_area_id', 'left');

        // Filtro por texto
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $builder->groupStart()
                   ->like('projects.code', $search)
                   ->orLike('projects.title', $search)
                   ->orLike('projects.description', $search)
                   ->orLike('users.full_name', $search)
                   ->groupEnd();
        }

        // Filtro por estado
        if (!empty($filters['status'])) {
            $builder->where('projects.status', $filters['status']);
        }

        // Filtro por prioridad
        if (!empty($filters['priority'])) {
            $builder->where('projects.priority', $filters['priority']);
        }

        // Filtro por área actual
        if (!empty($filters['area_id'])) {
            $builder->where('projects.current_area_id', $filters['area_id']);
        }

        // Filtro por solicitante
        if (!empty($filters['requester_id'])) {
            $builder->where('projects.requester_id', $filters['requester_id']);
        }

        // Filtro por rango de fechas
        if (!empty($filters['date_from'])) {
            $builder->where('projects.created_at >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('projects.created_at <=', $filters['date_to']);
        }

        // Filtro por departamento
        if (!empty($filters['department'])) {
            $builder->like('projects.department', $filters['department']);
        }

        // Ordenamiento
        $orderBy = $filters['order_by'] ?? 'projects.created_at';
        $orderDir = $filters['order_dir'] ?? 'DESC';
        $builder->orderBy($orderBy, $orderDir);

        // Límite
        if (!empty($filters['limit'])) {
            $builder->limit($filters['limit']);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Avanzar proyecto a la siguiente área
     */
    public function moveToNextArea(int $projectId): bool
    {
        $project = $this->find($projectId);
        if (!$project) {
            return false;
        }

        $areaModel = new \App\Models\AreaModel();
        $nextArea = $areaModel->getNextArea($project['current_area_id']);

        $this->db->transStart();

        if ($nextArea) {
            // Mover a siguiente área
            $this->update($projectId, [
                'current_area_id' => $nextArea['id'],
                'status' => 'in_progress'
            ]);

            // Crear fase para la nueva área
            $phaseModel = new \App\Models\ProjectPhaseModel();
            $phaseModel->createPhaseForProject($projectId, $nextArea['id']);
        } else {
            // No hay más áreas, completar proyecto
            $this->update($projectId, [
                'current_area_id' => null,
                'status' => 'completed',
                'completion_percentage' => 100.00,
                'actual_completion' => date('Y-m-d H:i:s')
            ]);
        }

        $this->db->transComplete();
        return $this->db->transStatus();
    }

    /**
     * Rechazar proyecto
     */
    public function rejectProject(int $projectId, string $reason, int $rejectedBy): bool
    {
        $this->db->transStart();

        // Actualizar estado del proyecto
        $this->update($projectId, [
            'status' => 'rejected',
            'additional_info' => json_encode([
                'rejection_reason' => $reason,
                'rejected_by' => $rejectedBy,
                'rejected_at' => date('Y-m-d H:i:s')
            ])
        ]);

        // Registrar en auditoría
        $auditModel = new \App\Models\AuditLogModel();
        $auditModel->logEvent([
            'user_id' => $rejectedBy,
            'project_id' => $projectId,
            'action' => 'project_rejected',
            'description' => "Proyecto rechazado. Motivo: {$reason}",
            'risk_level' => 'medium',
            'success' => true
        ]);

        $this->db->transComplete();
        return $this->db->transStatus();
    }

    /**
     * Pausar proyecto
     */
    public function pauseProject(int $projectId, string $reason, int $pausedBy): bool
    {
        return $this->update($projectId, [
            'status' => 'on_hold',
            'additional_info' => json_encode([
                'pause_reason' => $reason,
                'paused_by' => $pausedBy,
                'paused_at' => date('Y-m-d H:i:s')
            ])
        ]);
    }

    /**
     * Reanudar proyecto pausado
     */
    public function resumeProject(int $projectId, int $resumedBy): bool
    {
        $project = $this->find($projectId);
        if (!$project || $project['status'] !== 'on_hold') {
            return false;
        }

        return $this->update($projectId, [
            'status' => $project['current_area_id'] ? 'in_progress' : 'submitted',
            'additional_info' => json_encode([
                'resumed_by' => $resumedBy,
                'resumed_at' => date('Y-m-d H:i:s')
            ])
        ]);
    }

    /**
     * Obtener estadísticas generales de proyectos
     */
    public function getProjectStats(): array
    {
        return [
            'total' => $this->countAll(),
            'active' => $this->whereIn('status', ['submitted', 'in_progress'])->countAllResults(),
            'completed' => $this->where('status', 'completed')->countAllResults(),
            'on_hold' => $this->where('status', 'on_hold')->countAllResults(),
            'rejected' => $this->where('status', 'rejected')->countAllResults(),
            'high_priority' => $this->where('priority', 'high')->countAllResults(),
            'overdue' => $this->getOverdueProjectsCount(),
            'avg_completion_days' => $this->getAverageCompletionDays(),
            'by_status' => $this->getProjectCountByStatus(),
            'by_priority' => $this->getProjectCountByPriority(),
            'by_area' => $this->getProjectCountByArea()
        ];
    }

    /**
     * Obtener proyectos vencidos
     */
    public function getOverdueProjects(): array
    {
        return $this->select('projects.*, users.full_name as requester_name')
                   ->join('users', 'users.id = projects.requester_id')
                   ->where('projects.estimated_completion <', date('Y-m-d'))
                   ->whereIn('projects.status', ['submitted', 'in_progress'])
                   ->orderBy('projects.estimated_completion', 'ASC')
                   ->findAll();
    }

    /**
     * Contar proyectos vencidos
     */
    public function getOverdueProjectsCount(): int
    {
        return $this->where('estimated_completion <', date('Y-m-d'))
                   ->whereIn('status', ['submitted', 'in_progress'])
                   ->countAllResults();
    }

    /**
     * Obtener promedio de días de completación
     */
    public function getAverageCompletionDays(): float
    {
        $builder = $this->builder();
        $builder->select('AVG(DATEDIFF(actual_completion, created_at)) as avg_days')
               ->where('status', 'completed')
               ->where('actual_completion IS NOT NULL');
               
        $result = $builder->get()->getRow();
        return $result ? (float)$result->avg_days : 0.0;
    }

    /**
     * Obtener conteo por estado
     */
    public function getProjectCountByStatus(): array
    {
        return $this->select('status, COUNT(*) as count')
                   ->groupBy('status')
                   ->orderBy('count', 'DESC')
                   ->findAll();
    }

    /**
     * Obtener conteo por prioridad
     */
    public function getProjectCountByPriority(): array
    {
        return $this->select('priority, COUNT(*) as count')
                   ->groupBy('priority')
                   ->orderBy('count', 'DESC')
                   ->findAll();
    }

    /**
     * Obtener conteo por área
     */
    public function getProjectCountByArea(): array
    {
        return $this->select('areas.name as area_name, COUNT(*) as count')
                   ->join('areas', 'areas.id = projects.current_area_id', 'left')
                   ->groupBy('projects.current_area_id')
                   ->orderBy('count', 'DESC')
                   ->findAll();
    }

    /**
     * Obtener proyectos recientes
     */
    public function getRecentProjects(int $limit = 10): array
    {
        return $this->select('projects.*, users.full_name as requester_name')
                   ->join('users', 'users.id = projects.requester_id')
                   ->orderBy('projects.created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Obtener proyectos próximos a vencer
     */
    public function getProjectsDueSoon(int $days = 7): array
    {
        $dueDate = date('Y-m-d', strtotime("+{$days} days"));
        
        return $this->select('projects.*, users.full_name as requester_name, areas.name as current_area_name')
                   ->join('users', 'users.id = projects.requester_id')
                   ->join('areas', 'areas.id = projects.current_area_id', 'left')
                   ->where('projects.estimated_completion <=', $dueDate)
                   ->where('projects.estimated_completion >=', date('Y-m-d'))
                   ->whereIn('projects.status', ['submitted', 'in_progress'])
                   ->orderBy('projects.estimated_completion', 'ASC')
                   ->findAll();
    }

    /**
     * Actualizar porcentaje de completación
     */
    public function updateCompletionPercentage(int $projectId): bool
    {
        $phaseModel = new \App\Models\ProjectPhaseModel();
        $phases = $phaseModel->getProjectPhases($projectId);
        
        if (empty($phases)) {
            return false;
        }

        $completedPhases = 0;
        foreach ($phases as $phase) {
            if ($phase['status'] === 'completed') {
                $completedPhases++;
            }
        }

        $percentage = ($completedPhases / count($phases)) * 100;
        
        return $this->update($projectId, [
            'completion_percentage' => round($percentage, 2)
        ]);
    }

    /**
     * Obtener información completa del proyecto
     */
    public function getProjectWithDetails(int $projectId): ?array
    {
        $project = $this->select('projects.*, users.full_name as requester_name, users.email as requester_email, areas.name as current_area_name, areas.color as current_area_color')
                       ->join('users', 'users.id = projects.requester_id')
                       ->join('areas', 'areas.id = projects.current_area_id', 'left')
                       ->where('projects.id', $projectId)
                       ->first();

        if (!$project) {
            return null;
        }

        // Agregar fases del proyecto
        $phaseModel = new \App\Models\ProjectPhaseModel();
        $project['phases'] = $phaseModel->getProjectPhases($projectId);

        // Agregar documentos del proyecto
        $documentModel = new \App\Models\DocumentModel();
        $project['documents'] = $documentModel->getProjectDocuments($projectId);

        return $project;
    }

    /**
     * Validar si se puede eliminar un proyecto
     */
    public function canDelete(int $projectId): array
    {
        $project = $this->find($projectId);
        $issues = [];

        if (!$project) {
            $issues[] = 'El proyecto no existe';
            return ['can_delete' => false, 'issues' => $issues];
        }

        // No se puede eliminar si está en progreso
        if (in_array($project['status'], ['in_progress', 'completed'])) {
            $issues[] = 'No se pueden eliminar proyectos en progreso o completados';
        }

        // Verificar si tiene documentos
        $documentModel = new \App\Models\DocumentModel();
        $documentCount = $documentModel->where('project_id', $projectId)->countAllResults();
        
        if ($documentCount > 0) {
            $issues[] = "El proyecto tiene {$documentCount} documentos asociados";
        }

        return [
            'can_delete' => empty($issues),
            'issues' => $issues
        ];
    }
}