<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo de Fases de Proyecto
 * Sistema Multi-Área Universidad Católica
 */
class ProjectPhaseModel extends Model
{
    protected $table = 'project_phases';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'project_id',
        'area_id',
        'phase_order',
        'status',
        'assigned_to',
        'estimated_hours',
        'actual_hours',
        'started_at',
        'completed_at',
        'due_date',
        'notes',
        'rejection_reason'
    ];

    // Fechas
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = null;

    // Validación
    protected $validationRules = [
        'project_id' => 'required|integer|is_not_unique[projects.id]',
        'area_id' => 'required|integer|is_not_unique[areas.id]',
        'phase_order' => 'required|integer|greater_than[0]',
        'status' => 'required|in_list[pending,assigned,in_progress,completed,rejected,skipped]',
        'estimated_hours' => 'permit_empty|decimal|greater_than[0]',
        'actual_hours' => 'permit_empty|decimal|greater_than[0]'
    ];

    protected $validationMessages = [
        'project_id' => [
            'is_not_unique' => 'El proyecto especificado no existe'
        ],
        'area_id' => [
            'is_not_unique' => 'El área especificada no existe'
        ],
        'status' => [
            'in_list' => 'El estado debe ser: pending, assigned, in_progress, completed, rejected, skipped'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeUpdate = ['updateTimestamps'];

    /**
     * Actualizar timestamps según el estado
     */
    protected function updateTimestamps(array $data): array
    {
        if (isset($data['data']['status'])) {
            $status = $data['data']['status'];
            $now = date('Y-m-d H:i:s');

            switch ($status) {
                case 'in_progress':
                    if (empty($data['data']['started_at'])) {
                        $data['data']['started_at'] = $now;
                    }
                    break;

                case 'completed':
                case 'rejected':
                case 'skipped':
                    if (empty($data['data']['completed_at'])) {
                        $data['data']['completed_at'] = $now;
                    }
                    break;
            }
        }

        return $data;
    }

    /**
     * Crear fases para un proyecto según las áreas activas
     */
    public function createPhasesForProject(int $projectId): bool
    {
        $areaModel = new \App\Models\AreaModel();
        $areas = $areaModel->getActiveAreas();

        if (empty($areas)) {
            return false;
        }

        $this->db->transStart();

        foreach ($areas as $index => $area) {
            $this->insert([
                'project_id' => $projectId,
                'area_id' => $area['id'],
                'phase_order' => $index + 1,
                'status' => $index === 0 ? 'pending' : 'pending',
                'estimated_hours' => $area['estimated_days'] * 8, // Convertir días a horas
                'due_date' => $this->calculateDueDate($projectId, $index + 1, $area['estimated_days'])
            ]);
        }

        $this->db->transComplete();
        return $this->db->transStatus();
    }

    /**
     * Crear una fase específica para un proyecto
     */
    public function createPhaseForProject(int $projectId, int $areaId): bool
    {
        $areaModel = new \App\Models\AreaModel();
        $area = $areaModel->find($areaId);

        if (!$area) {
            return false;
        }

        // Obtener el orden de la fase
        $phaseOrder = $this->where('project_id', $projectId)->countAllResults() + 1;

        return $this->insert([
            'project_id' => $projectId,
            'area_id' => $areaId,
            'phase_order' => $phaseOrder,
            'status' => 'pending',
            'estimated_hours' => $area['estimated_days'] * 8,
            'due_date' => $this->calculateDueDate($projectId, $phaseOrder, $area['estimated_days'])
        ]) !== false;
    }

    /**
     * Calcular fecha de vencimiento para una fase
     */
    protected function calculateDueDate(int $projectId, int $phaseOrder, int $estimatedDays): string
    {
        // Obtener fases anteriores
        $previousPhases = $this->where('project_id', $projectId)
                              ->where('phase_order <', $phaseOrder)
                              ->orderBy('phase_order', 'ASC')
                              ->findAll();

        $startDate = date('Y-m-d');
        
        if (!empty($previousPhases)) {
            // Sumar días estimados de fases anteriores
            $totalDays = 0;
            foreach ($previousPhases as $phase) {
                $totalDays += ceil(($phase['estimated_hours'] ?? 0) / 8);
            }
            $startDate = date('Y-m-d', strtotime("+{$totalDays} days"));
        }

        return date('Y-m-d', strtotime($startDate . " +{$estimatedDays} days"));
    }

    /**
     * Obtener fases de un proyecto
     */
    public function getProjectPhases(int $projectId): array
    {
        return $this->select('project_phases.*, areas.name as area_name, areas.color as area_color, areas.icon as area_icon, users.full_name as assigned_to_name')
                   ->join('areas', 'areas.id = project_phases.area_id')
                   ->join('users', 'users.id = project_phases.assigned_to', 'left')
                   ->where('project_phases.project_id', $projectId)
                   ->orderBy('project_phases.phase_order', 'ASC')
                   ->findAll();
    }

    /**
     * Obtener fase actual de un proyecto
     */
    public function getCurrentPhase(int $projectId): ?array
    {
        return $this->select('project_phases.*, areas.name as area_name, areas.color as area_color')
                   ->join('areas', 'areas.id = project_phases.area_id')
                   ->where('project_phases.project_id', $projectId)
                   ->whereIn('project_phases.status', ['pending', 'assigned', 'in_progress'])
                   ->orderBy('project_phases.phase_order', 'ASC')
                   ->first();
    }

    /**
     * Obtener siguiente fase de un proyecto
     */
    public function getNextPhase(int $projectId, int $currentPhaseOrder): ?array
    {
        return $this->select('project_phases.*, areas.name as area_name')
                   ->join('areas', 'areas.id = project_phases.area_id')
                   ->where('project_phases.project_id', $projectId)
                   ->where('project_phases.phase_order >', $currentPhaseOrder)
                   ->orderBy('project_phases.phase_order', 'ASC')
                   ->first();
    }

    /**
     * Asignar fase a un usuario
     */
    public function assignPhase(int $phaseId, int $userId): bool
    {
        return $this->update($phaseId, [
            'assigned_to' => $userId,
            'status' => 'assigned'
        ]);
    }

    /**
     * Iniciar trabajo en una fase
     */
    public function startPhase(int $phaseId, ?int $userId = null): bool
    {
        $updateData = [
            'status' => 'in_progress',
            'started_at' => date('Y-m-d H:i:s')
        ];

        if ($userId) {
            $updateData['assigned_to'] = $userId;
        }

        return $this->update($phaseId, $updateData);
    }

    /**
     * Completar una fase
     */
    public function completePhase(int $phaseId, float $actualHours = null, string $notes = null): bool
    {
        $updateData = [
            'status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s')
        ];

        if ($actualHours !== null) {
            $updateData['actual_hours'] = $actualHours;
        }

        if ($notes !== null) {
            $updateData['notes'] = $notes;
        }

        $this->db->transStart();

        // Actualizar la fase
        $this->update($phaseId, $updateData);

        // Obtener información de la fase para avanzar el proyecto
        $phase = $this->find($phaseId);
        if ($phase) {
            $projectModel = new \App\Models\ProjectModel();
            $projectModel->updateCompletionPercentage($phase['project_id']);

            // Verificar si es la última fase
            $nextPhase = $this->getNextPhase($phase['project_id'], $phase['phase_order']);
            if (!$nextPhase) {
                // Es la última fase, completar proyecto
                $projectModel->update($phase['project_id'], [
                    'status' => 'completed',
                    'completion_percentage' => 100.00,
                    'actual_completion' => date('Y-m-d H:i:s'),
                    'current_area_id' => null
                ]);
            } else {
                // Avanzar a la siguiente fase
                $projectModel->update($phase['project_id'], [
                    'current_area_id' => $nextPhase['area_id']
                ]);
                
                // Marcar siguiente fase como pendiente si no está asignada
                if ($nextPhase['status'] === 'pending') {
                    $this->update($nextPhase['id'], ['status' => 'pending']);
                }
            }
        }

        $this->db->transComplete();
        return $this->db->transStatus();
    }

    /**
     * Rechazar una fase
     */
    public function rejectPhase(int $phaseId, string $reason, int $rejectedBy): bool
    {
        $this->db->transStart();

        // Actualizar la fase
        $this->update($phaseId, [
            'status' => 'rejected',
            'completed_at' => date('Y-m-d H:i:s'),
            'rejection_reason' => $reason
        ]);

        // Obtener información de la fase
        $phase = $this->find($phaseId);
        if ($phase) {
            // Rechazar el proyecto completo
            $projectModel = new \App\Models\ProjectModel();
            $projectModel->rejectProject($phase['project_id'], "Rechazado en fase: {$reason}", $rejectedBy);
        }

        $this->db->transComplete();
        return $this->db->transStatus();
    }

    /**
     * Saltar una fase (marcarla como no aplicable)
     */
    public function skipPhase(int $phaseId, string $reason, int $skippedBy): bool
    {
        return $this->update($phaseId, [
            'status' => 'skipped',
            'completed_at' => date('Y-m-d H:i:s'),
            'notes' => "Fase saltada por: {$reason}",
            'assigned_to' => $skippedBy
        ]);
    }

    /**
     * Obtener fases asignadas a un usuario
     */
    public function getUserAssignedPhases(int $userId): array
    {
        return $this->select('project_phases.*, projects.code as project_code, projects.title as project_title, areas.name as area_name, areas.color as area_color')
                   ->join('projects', 'projects.id = project_phases.project_id')
                   ->join('areas', 'areas.id = project_phases.area_id')
                   ->where('project_phases.assigned_to', $userId)
                   ->whereIn('project_phases.status', ['assigned', 'in_progress'])
                   ->orderBy('project_phases.due_date', 'ASC')
                   ->findAll();
    }

    /**
     * Obtener fases por área
     */
    public function getPhasesByArea(int $areaId, array $filters = []): array
    {
        $builder = $this->select('project_phases.*, projects.code as project_code, projects.title as project_title, projects.priority, users.full_name as assigned_to_name')
                       ->join('projects', 'projects.id = project_phases.project_id')
                       ->join('users', 'users.id = project_phases.assigned_to', 'left')
                       ->where('project_phases.area_id', $areaId);

        // Filtros
        if (!empty($filters['status'])) {
            $builder->where('project_phases.status', $filters['status']);
        }

        if (!empty($filters['assigned_to'])) {
            $builder->where('project_phases.assigned_to', $filters['assigned_to']);
        }

        if (!empty($filters['overdue'])) {
            $builder->where('project_phases.due_date <', date('Y-m-d'))
                   ->whereIn('project_phases.status', ['pending', 'assigned', 'in_progress']);
        }

        // Ordenamiento
        $orderBy = $filters['order_by'] ?? 'project_phases.created_at';
        $orderDir = $filters['order_dir'] ?? 'ASC';
        $builder->orderBy($orderBy, $orderDir);

        return $builder->get()->getResultArray();
    }

    /**
     * Obtener fases vencidas
     */
    public function getOverduePhases(): array
    {
        return $this->select('project_phases.*, projects.code as project_code, projects.title as project_title, areas.name as area_name, users.full_name as assigned_to_name')
                   ->join('projects', 'projects.id = project_phases.project_id')
                   ->join('areas', 'areas.id = project_phases.area_id')
                   ->join('users', 'users.id = project_phases.assigned_to', 'left')
                   ->where('project_phases.due_date <', date('Y-m-d'))
                   ->whereIn('project_phases.status', ['pending', 'assigned', 'in_progress'])
                   ->orderBy('project_phases.due_date', 'ASC')
                   ->findAll();
    }

    /**
     * Obtener estadísticas de fases
     */
    public function getPhaseStats(int $areaId = null): array
    {
        $builder = $this->builder();
        
        if ($areaId) {
            $builder->where('area_id', $areaId);
        }

        $stats = [
            'total' => $builder->countAllResults(false),
            'pending' => $builder->where('status', 'pending')->countAllResults(false),
            'assigned' => $builder->where('status', 'assigned')->countAllResults(false),
            'in_progress' => $builder->where('status', 'in_progress')->countAllResults(false),
            'completed' => $builder->where('status', 'completed')->countAllResults(false),
            'rejected' => $builder->where('status', 'rejected')->countAllResults(false),
            'overdue' => $this->getOverdueCount($areaId),
            'avg_completion_hours' => $this->getAverageCompletionHours($areaId)
        ];

        return $stats;
    }

    /**
     * Obtener conteo de fases vencidas
     */
    public function getOverdueCount(int $areaId = null): int
    {
        $builder = $this->builder();
        
        if ($areaId) {
            $builder->where('area_id', $areaId);
        }

        return $builder->where('due_date <', date('Y-m-d'))
                      ->whereIn('status', ['pending', 'assigned', 'in_progress'])
                      ->countAllResults();
    }

    /**
     * Obtener promedio de horas de completación
     */
    public function getAverageCompletionHours(int $areaId = null): float
    {
        $builder = $this->builder();
        $builder->select('AVG(actual_hours) as avg_hours')
               ->where('status', 'completed')
               ->where('actual_hours IS NOT NULL');

        if ($areaId) {
            $builder->where('area_id', $areaId);
        }

        $result = $builder->get()->getRow();
        return $result ? (float)$result->avg_hours : 0.0;
    }

    /**
     * Obtener fases próximas a vencer
     */
    public function getPhasesDueSoon(int $days = 3, int $areaId = null): array
    {
        $dueDate = date('Y-m-d', strtotime("+{$days} days"));
        
        $builder = $this->select('project_phases.*, projects.code as project_code, projects.title as project_title, areas.name as area_name, users.full_name as assigned_to_name')
                       ->join('projects', 'projects.id = project_phases.project_id')
                       ->join('areas', 'areas.id = project_phases.area_id')
                       ->join('users', 'users.id = project_phases.assigned_to', 'left')
                       ->where('project_phases.due_date <=', $dueDate)
                       ->where('project_phases.due_date >=', date('Y-m-d'))
                       ->whereIn('project_phases.status', ['pending', 'assigned', 'in_progress']);

        if ($areaId) {
            $builder->where('project_phases.area_id', $areaId);
        }

        return $builder->orderBy('project_phases.due_date', 'ASC')->findAll();
    }

    /**
     * Actualizar fecha de vencimiento de una fase
     */
    public function updateDueDate(int $phaseId, string $newDueDate, string $reason = null): bool
    {
        $updateData = ['due_date' => $newDueDate];
        
        if ($reason) {
            $updateData['notes'] = ($this->find($phaseId)['notes'] ?? '') . "\nFecha actualizada: {$reason}";
        }

        return $this->update($phaseId, $updateData);
    }

    /**
     * Transferir asignación de fase entre usuarios
     */
    public function transferPhaseAssignment(int $phaseId, int $newUserId, int $transferredBy, string $reason = null): bool
    {
        $phase = $this->find($phaseId);
        if (!$phase) {
            return false;
        }

        $notes = ($phase['notes'] ?? '') . "\nTransferido a nuevo usuario";
        if ($reason) {
            $notes .= ": {$reason}";
        }

        return $this->update($phaseId, [
            'assigned_to' => $newUserId,
            'notes' => $notes
        ]);
    }

    /**
     * Obtener resumen de progreso de un proyecto
     */
    public function getProjectProgress(int $projectId): array
    {
        $phases = $this->getProjectPhases($projectId);
        
        $progress = [
            'total_phases' => count($phases),
            'completed_phases' => 0,
            'current_phase' => null,
            'next_phase' => null,
            'completion_percentage' => 0,
            'estimated_total_hours' => 0,
            'actual_total_hours' => 0,
            'phases_by_status' => [
                'pending' => 0,
                'assigned' => 0,
                'in_progress' => 0,
                'completed' => 0,
                'rejected' => 0,
                'skipped' => 0
            ]
        ];

        foreach ($phases as $phase) {
            $progress['phases_by_status'][$phase['status']]++;
            $progress['estimated_total_hours'] += $phase['estimated_hours'] ?? 0;
            $progress['actual_total_hours'] += $phase['actual_hours'] ?? 0;

            if ($phase['status'] === 'completed') {
                $progress['completed_phases']++;
            } elseif (in_array($phase['status'], ['pending', 'assigned', 'in_progress']) && !$progress['current_phase']) {
                $progress['current_phase'] = $phase;
            }
        }

        if ($progress['total_phases'] > 0) {
            $progress['completion_percentage'] = ($progress['completed_phases'] / $progress['total_phases']) * 100;
        }

        return $progress;
    }
}